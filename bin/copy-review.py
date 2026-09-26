#!/usr/bin/env python3
"""
Readability and prose review of the site copy and the drafts.

Reports what Hemingway reports (grade level, hard sentences, adverbs, passive
voice) plus proselint and write-good, over every page at once.

    python3 bin/copy-review.py            # summary
    python3 bin/copy-review.py --detail   # every flagged sentence

First run needs the two linters, neither of which ships in the theme:

    npm install            # write-good
    pip install proselint

Both are optional. Without them the readability figures and the hard-sentence
list still work, since those are computed here.

These tools are blunt instruments for marketing copy. They score plain
expository prose; they will flag a deliberate fragment, a passive that reads
better than its active ("Built from scratch"), and an adverb doing real work.
Treat the output as a detector, not a verdict: findings under LIKELY VOICE are
listed so they can be dismissed on purpose rather than by accident.
"""

import json
import re
import subprocess
import sys
import textwrap
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SCHEMA = ROOT / 'studiogreen' / 'inc' / 'fields-schema.php'
POSTS = ROOT / 'posts'
DETAIL = '--detail' in sys.argv

# Field keys that hold no prose.
SKIP = ('_url', '_id', 'typeform', 'email', 'updated', 'meta_description', 'hero_words')

# Deliberate register on this site, so not worth flagging as a problem.
VOICE_ADVERBS = {'actually', 'genuinely', 'exactly', 'properly', 'directly', 'quietly', 'usually'}


def page_copy():
    """Site copy per template, from the real schema via bin/dump-copy.php."""
    try:
        r = subprocess.run(['php', str(ROOT / 'bin' / 'dump-copy.php')],
                           capture_output=True, text=True, timeout=60)
        data = json.loads(r.stdout)
    except Exception as e:
        print('could not read the schema: %s' % e, file=sys.stderr)
        return {}

    label = {'home': 'Homepage', 'web': 'Web page', 'design': 'Design page',
             'service': 'Service template', 'about': 'About page',
             'contact': 'Contact page', 'legal': 'Legal pages'}

    out = {}
    for template, fields in data.items():
        units = []
        for f in fields:
            txt = f['text'].replace('==', '')
            if f['type'] == 'list':
                # One item per line; each stands alone rather than running on.
                for line in txt.split('\n'):
                    if len(line.split()) >= 3:
                        units.append((f['key'], line.strip()))
                continue
            units.append((f['key'], txt))
        if units:
            out[label.get(template, template)] = units
    return out


def post_copy():
    """Post bodies, with markdown structure turned into separate units."""
    out = {}
    for f in sorted(POSTS.glob('*.md')):
        raw = f.read_text().split('---', 2)[-1]
        units = []
        for line in raw.splitlines():
            line = line.strip()
            if not line or line.startswith('|') or set(line) <= set('-| '):
                continue
            # Link text only, and drop markdown furniture.
            line = re.sub(r'\[([^\]]+)\]\([^)]+\)', r'\1', line)
            line = re.sub(r'^[#>\-\d.]+\s*', '', line)
            line = line.replace('**', '').replace('*', '').replace('`', '')
            line = ' '.join(line.split())
            if len(line.split()) < 3:
                continue
            # Headings and list items carry no full stop; give them one so they
            # are measured alone rather than welded to the sentence after them.
            if not line.endswith(('.', '!', '?', ':')):
                line += '.'
            units.append(('body', line))
        if units:
            out[f.name] = units
    return out


def sentences(text):
    parts = re.split(r'(?<=[.!?])\s+', text.strip())
    return [p.strip() for p in parts if len(p.split()) > 1]


def run_write_good(text):
    node = ROOT / 'node_modules' / 'write-good'
    if not node.exists():
        return []
    script = (
        "const wg=require(%s);"
        "let d='';process.stdin.on('data',c=>d+=c).on('end',()=>"
        "console.log(JSON.stringify(wg(d,{weasel:true,illusion:true,so:true,"
        "thereIs:true,passive:true,adverb:true,tooWordy:true,cliches:true}))));"
        % json.dumps(str(node))
    )
    try:
        r = subprocess.run([ 'node', '-e', script ], input=text, capture_output=True,
                           text=True, timeout=60)
        return json.loads(r.stdout or '[]')
    except Exception:
        return []


def run_proselint(text):
    try:
        from proselint.tools import lint
        return lint(text)
    except Exception:
        return []


def syllables(word):
    """Vowel-group heuristic. Good enough for an aggregate score."""
    w = re.sub(r'[^a-z]', '', word.lower())
    if not w:
        return 0
    n = len(re.findall(r'[aeiouy]+', w))
    if w.endswith('e') and not w.endswith(('le', 'ee', 'ye')) and n > 1:
        n -= 1
    return max(1, n)


def grade(text):
    """ARI and Coleman-Liau need no syllables; Flesch uses the heuristic above."""
    words = re.findall(r"[A-Za-z0-9']+", text)
    nw = len(words)
    ns = max(1, len(sentences(text)))
    nc = sum(len(w) for w in words)
    nsyl = sum(syllables(w) for w in words)
    if not nw:
        return {'words': 0, 'sentences': 0, 'ari': 0, 'cl': 0, 'ease': 0}
    ari = 4.71 * (nc / nw) + 0.5 * (nw / ns) - 21.43
    L = nc / nw * 100
    S = ns / nw * 100
    cl = 0.0588 * L - 0.296 * S - 15.8
    ease = 206.835 - 1.015 * (nw / ns) - 84.6 * (nsyl / nw)
    return {'words': nw, 'sentences': ns, 'ari': ari, 'cl': cl, 'ease': ease}


def review(name, fields):
    # Each field ends with a full stop for measurement, so headings without
    # terminal punctuation are not joined onto whatever follows them.
    text = ' '.join(v if v.rstrip().endswith(('.', '!', '?')) else v.rstrip() + '.'
                    for _, v in fields)
    if not text.strip():
        return None

    g = grade(text)
    sents = sentences(text)

    hard, very_hard = [], []
    for s in sents:
        if len(s.split()) < 12:
            continue
        sg = grade(s)['ari']
        if sg >= 14:
            very_hard.append((round(sg), s))
        elif sg >= 10:
            hard.append((round(sg), s))

    adverbs = [w for w in re.findall(r"\b\w+ly\b", text.lower())
               if w not in ('only', 'family', 'likely', 'early', 'reply', 'apply')]
    voice_adv = [a for a in adverbs if a in VOICE_ADVERBS]
    real_adv = [a for a in adverbs if a not in VOICE_ADVERBS]

    wg = run_write_good(text)
    passive = [x for x in wg if 'passive voice' in x.get('reason', '').lower()]
    weasel = [x for x in wg if 'weasel' in x.get('reason', '').lower()]
    wordy = [x for x in wg if 'wordy' in x.get('reason', '').lower()
             or 'cliche' in x.get('reason', '').lower()]
    pl = run_proselint(text)

    return {
        'name': name, 'g': g, 'sents': sents,
        'hard': hard, 'very_hard': very_hard,
        'real_adv': real_adv, 'voice_adv': voice_adv,
        'passive': passive, 'weasel': weasel, 'wordy': wordy, 'proselint': pl,
        'longest': sorted(sents, key=lambda s: -len(s.split()))[:3],
    }


def main():
    docs = {}
    docs.update(page_copy())
    docs.update(post_copy())

    results = [r for r in (review(n, f) for n, f in docs.items() if f) if r]
    if not results:
        print('No copy found.')
        return 1

    print('=' * 78)
    print('READABILITY  (ARI and C-L are US grade levels; 8-10 reads easily for everyone)')
    print('=' * 78)
    print('%-34s %5s %5s %6s %6s %6s' % ('', 'words', 'sents', 'ARI', 'C-L', 'ease'))
    for r in results:
        g = r['g']
        print('%-34s %5d %5d %6.1f %6.1f %6.1f' % (
            r['name'][:34], g['words'], g['sentences'], g['ari'], g['cl'], g['ease']))

    print()
    print('=' * 78)
    print('FLAGS')
    print('=' * 78)
    print('%-34s %5s %5s %5s %5s %5s %5s' % (
        '', 'v.hard', 'hard', 'adv', 'pass', 'weak', 'lint'))
    for r in results:
        print('%-34s %5d %5d %5d %5d %5d %5d' % (
            r['name'][:34], len(r['very_hard']), len(r['hard']), len(r['real_adv']),
            len(r['passive']), len(r['weasel']) + len(r['wordy']), len(r['proselint'])))

    print()
    print('=' * 78)
    print('WORTH LOOKING AT')
    print('=' * 78)
    any_found = False
    for r in results:
        items = []
        for sg, s in r['very_hard']:
            items.append(('very hard sentence (grade %d)' % sg, s))
        if DETAIL:
            for sg, s in r['hard']:
                items.append(('hard sentence (grade %d)' % sg, s))
        for x in r['weasel'] + r['wordy']:
            items.append((x['reason'], ''))
        for p in r['proselint']:
            items.append((p[2] if len(p) > 2 else str(p), ''))
        if r['real_adv']:
            items.append(('adverbs: ' + ', '.join(sorted(set(r['real_adv']))), ''))
        if not items:
            continue
        any_found = True
        print('\n%s' % r['name'])
        for reason, s in items:
            print('  - %s' % reason)
            if s:
                for line in textwrap.wrap(s, 72):
                    print('      %s' % line)
    if not any_found:
        print('\n  Nothing flagged.')

    print()
    print('=' * 78)
    print('LIKELY VOICE, NOT A PROBLEM')
    print('=' * 78)
    for r in results:
        bits = []
        if r['voice_adv']:
            bits.append('deliberate adverbs: ' + ', '.join(sorted(set(r['voice_adv']))))
        if r['passive']:
            bits.append('%d passive (e.g. "Built from scratch", which reads better than the active)'
                        % len(r['passive']))
        if bits:
            print('\n%s' % r['name'])
            for b in bits:
                print('  - %s' % b)

    print()
    print('Longest sentences, for the read-aloud test:')
    for r in results:
        if r['longest']:
            s = r['longest'][0]
            print('\n  %s (%d words)' % (r['name'], len(s.split())))
            for line in textwrap.wrap(s, 72):
                print('    %s' % line)
    return 0


if __name__ == '__main__':
    sys.exit(main())

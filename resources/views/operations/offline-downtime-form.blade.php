<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NBTS controlled downtime collection form — {{ $identifier }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #171717; background: #eee; font: 12px/1.35 Arial, sans-serif; }
        main { width: 210mm; min-height: 297mm; margin: 10mm auto; padding: 11mm; background: white; }
        header { display: grid; grid-template-columns: 1fr auto; gap: 12mm; align-items: start; border-bottom: 2px solid #8f1236; padding-bottom: 5mm; }
        h1 { margin: 1mm 0; font-size: 20px; } h2 { margin: 0 0 3mm; font-size: 14px; }
        .eyebrow { color: #8f1236; font-size: 10px; font-weight: 700; letter-spacing: .12em; }
        .warning { margin: 5mm 0; padding: 3mm; border: 1px solid #d97706; background: #fffbeb; font-weight: 700; }
        .meta, .fields { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0; border: 1px solid #aaa; }
        .meta div, .field { min-height: 14mm; padding: 2.5mm; border-right: 1px solid #aaa; border-bottom: 1px solid #aaa; }
        .meta div:nth-child(2n), .field:nth-child(2n) { border-right: 0; }
        label, dt { display: block; color: #555; font-size: 9px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
        dd { margin: 1mm 0 0; font-weight: 700; }
        section { margin-top: 6mm; }
        .wide { grid-column: 1 / -1; min-height: 22mm; }
        .checks { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2mm; margin-top: 2mm; }
        .check { border: 1px solid #aaa; padding: 2mm; }
        footer { margin-top: 7mm; padding-top: 3mm; border-top: 1px solid #aaa; color: #555; font-size: 9px; }
        .barcode { text-align: center; } .barcode svg { max-width: 78mm; height: auto; }
        @media print { body { background: white; } main { margin: 0; } }
    </style>
</head>
<body>
<main>
    <header>
        <div><div class="eyebrow">NBTS / CONTROLLED DOWNTIME RECORD</div><h1>Offline whole-blood collection</h1><div>{{ $batch->bloodCenter->name }}</div></div>
        <div class="barcode">{!! $barcode !!}</div>
    </header>

    <div class="warning">CONSTRUCTION-ONLY FORM. Reconcile with the authoritative Laravel service before downstream release. Synchronization never makes a unit available.</div>

    <dl class="meta">
        <div><dt>Donation identifier</dt><dd>{{ $identifier }}</dd></div>
        <div><dt>Batch / sequence</dt><dd>{{ $batch->id }} / {{ $sequence }}</dd></div>
        <div><dt>Approved device</dt><dd>{{ $batch->device->name }} · {{ $batch->device->device_uuid }}</dd></div>
        <div><dt>Batch expiry</dt><dd>{{ $batch->expires_at->format('d M Y H:i') }}</dd></div>
    </dl>

    <section><h2>1. Donor and authority checks</h2><div class="fields">
        <div class="field"><label>Donor ID</label></div><div class="field"><label>Appointment / approved walk-in reference</label></div>
        <div class="field"><label>Identity method and evidence suffix</label></div><div class="field"><label>Identity confirmed by / time</label></div>
        <div class="field"><label>Screening decision / protocol version</label></div><div class="field"><label>Screening officer / time</label></div>
    </div></section>

    <section><h2>2. Collection and specimens</h2><div class="fields">
        <div class="field"><label>Bag configuration</label></div><div class="field"><label>Manufacturer bag lot</label></div>
        <div class="field"><label>Collection device / scale</label></div><div class="field"><label>Planned volume (ml)</label></div>
        <div class="field"><label>Start time</label></div><div class="field"><label>End time</label></div>
        <div class="field"><label>Measured volume (ml)</label></div><div class="field"><label>Outcome</label></div>
        <div class="field"><label>Serology specimen scan / volume</label></div><div class="field"><label>EDTA specimen scan / volume</label></div>
    </div></section>

    <section><h2>3. Donor care and exceptions</h2><div class="fields">
        <div class="field wide"><label>Reaction, symptoms, treatment, referral and outcome</label></div>
        <div class="field"><label>Aftercare confirmed by / time</label></div><div class="field"><label>Donor acknowledgement</label></div>
        <div class="field wide"><label>Exception, interruption, label replacement or reconciliation notes</label></div>
    </div></section>

    <section><h2>4. Controlled reconciliation</h2><div class="checks"><div class="check">□ Account active</div><div class="check">□ Identity current</div><div class="check">□ Duplicate clear</div><div class="check">□ Screening current</div><div class="check">□ Deferral clear</div><div class="check">□ Interval clear</div><div class="check">□ Identifier unique</div><div class="check">□ Labels matched</div><div class="check">□ Quarantine confirmed</div></div></section>

    <footer>Generated {{ now()->format('d M Y H:i T') }} · Identifier standard {{ config('phase-six.identifiers.standard') }} · Template {{ config('phase-six.identifiers.label_template_version') }} · Retain and reconcile under the approved downtime procedure.</footer>
</main>
</body>
</html>

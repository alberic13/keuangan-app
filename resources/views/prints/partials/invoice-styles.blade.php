<style>
    @page { margin: 24px; }
    body { margin: 0; font-family: DejaVu Serif, serif; color: #1f2937; font-size: 10px; }
    .page { position: relative; background: #ffffff; padding: 30px 34px 20px; }
    .watermark {
        position: absolute; top: 0; right: 0; width: 170px; height: 170px; opacity: 0.04;
        background-image: radial-gradient(circle at 2px 2px, #00422f 1px, transparent 0); background-size: 20px 20px;
    }
    .header-shell { width: 100%; border-bottom: 4px double #14532d; padding-bottom: 16px; margin-bottom: 20px; }
    .header-shell td { vertical-align: top; }
    .seal-wrap { width: 72px; }
    .seal {
        width: 58px; height: 58px; border: 3px solid #14532d; border-radius: 999px;
        text-align: center; line-height: 58px; font-family: DejaVu Sans, sans-serif;
        font-weight: bold; color: #14532d; font-size: 16px; margin-top: 2px;
    }
    .header-copy { text-align: center; }
    .header-copy .kemenag { font-family: DejaVu Sans, sans-serif; font-size: 14px; text-transform: uppercase; letter-spacing: 0.7px; color: #14532d; font-weight: bold; }
    .header-copy .school { margin-top: 2px; font-size: 20px; font-weight: bold; text-transform: uppercase; color: #052e16; }
    .header-copy .address, .header-copy .meta-line { margin-top: 3px; font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #475569; }
    .title-block { text-align: center; margin-bottom: 18px; }
    .badge {
        display: inline-block; padding: 5px 10px; border-radius: 999px; background: #e7f3ec;
        color: #14532d; font-family: DejaVu Sans, sans-serif; font-size: 8px; font-weight: bold;
        letter-spacing: 1px; text-transform: uppercase; margin-bottom: 6px;
    }
    .title-block h1 { margin: 0; font-size: 15px; text-transform: uppercase; text-decoration: underline; text-underline-offset: 4px; }
    .title-block p { margin: 5px 0 0; font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #475569; text-transform: uppercase; }
    .meta-grid { width: 100%; margin-bottom: 14px; }
    .meta-grid td { width: 50%; vertical-align: top; padding-right: 8px; }
    .meta-card { border: 1px solid #d7e0db; background: #fbfdfb; padding: 12px 14px; min-height: 94px; }
    .meta-label { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; }
    .meta-value { margin-top: 4px; font-family: DejaVu Sans, sans-serif; font-size: 12px; font-weight: bold; color: #0f172a; }
    .pill { display: inline-block; padding: 4px 9px; border-radius: 999px; font-family: DejaVu Sans, sans-serif; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; }
    .pill-unpaid { background: #fee2e2; color: #991b1b; }
    .pill-partial { background: #fef3c7; color: #92400e; }
    .pill-paid { background: #dcfce7; color: #166534; }
    .pill-void { background: #e2e8f0; color: #334155; }
    .section-title { margin: 14px 0 8px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; color: #14532d; }
    .report-table { width: 100%; border-collapse: collapse; }
    .report-table th, .report-table td { border: 1px solid #cbd5cf; padding: 8px 9px; vertical-align: top; }
    .report-table th { background: #052e16; color: #ffffff; font-family: DejaVu Sans, sans-serif; font-size: 8px; text-transform: uppercase; letter-spacing: 0.8px; text-align: left; }
    .report-table td { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
    .text-right { text-align: right; }
    .summary-grid { width: 100%; margin-top: 12px; }
    .summary-grid td { width: 33.33%; padding-right: 8px; vertical-align: top; }
    .summary-card { border: 1px solid #d7e0db; background: #fbfdfb; padding: 12px 14px; }
    .summary-card .label { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; }
    .summary-card .value { margin-top: 4px; font-family: DejaVu Sans, sans-serif; font-size: 14px; font-weight: bold; color: #14532d; }
    .notes { margin-top: 12px; border: 1px solid #d7e0db; background: #fcfdfc; padding: 12px 14px; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
    .footer { margin-top: 18px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #94a3b8; }
    .footer table { width: 100%; }
    .footer .center { text-align: center; }
    .footer .right { text-align: right; }
    .page + .page { page-break-before: always; }
</style>

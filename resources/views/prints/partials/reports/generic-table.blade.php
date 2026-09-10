@if ($flatRows->isEmpty())
    <div class="empty">Tidak ada data untuk laporan ini.</div>
@else
    @php $headers = array_keys($flatRows->first()); @endphp
    <table class="report-table">
        <thead>
            <tr>
                @foreach ($headers as $header)
                    <th>{{ $formatHeading($header) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($flatRows as $row)
                <tr>
                    @foreach ($headers as $header)
                        <td class="{{ in_array($header, $currencyColumns, true) ? 'text-right' : '' }}">
                            {{ $formatValue($header, $row[$header] ?? null) }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

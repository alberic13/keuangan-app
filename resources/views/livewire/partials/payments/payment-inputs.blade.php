<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="payment_date" required type="date" value="{{ old('payment_date', now()->toDateString()) }}">

    <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="method" required>
        <option @selected(old('method', 'cash') === 'cash') value="cash">Tunai</option>
        <option @selected(old('method', 'cash') === 'bank_transfer') value="bank_transfer">Transfer Manual</option>
    </select>

    <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="cash_account_id" required>
        <option value="">Pilih akun kas/bank</option>
        @foreach ($accounts as $account)
            <option @selected((string) old('cash_account_id') === (string) $account->id) value="{{ $account->id }}">
                {{ $account->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="rounded-xl border border-surface-container bg-surface-container-low px-4 py-4">
    <label class="block text-sm font-semibold text-on-surface" for="payment_proof">Upload Bukti Pembayaran</label>
    <p class="mt-1 text-xs text-on-surface-variant">Format yang didukung: PDF, JPG, JPEG, PNG, atau WEBP. Wajib untuk metode transfer manual.</p>
    <input accept=".pdf,.jpg,.jpeg,.png,.webp" class="mt-3 block w-full rounded-xl border-none bg-white px-4 py-3 text-sm" id="payment_proof" name="payment_proof" type="file">
</div>

<textarea class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="notes" placeholder="Catatan transaksi" rows="3">{{ old('notes') }}</textarea>

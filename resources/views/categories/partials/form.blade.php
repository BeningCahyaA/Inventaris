@php
    $editing = isset($category);
    $categoryYear = now()->format('y');
@endphp
<div class="category-form-shell">
    <a href="{{ route('categories.index') }}" class="asset-back-link">← Kembali ke daftar kategori</a>
    <div class="category-form-heading">
        <h1>{{ $editing ? 'Edit kategori' : 'Tambah kategori baru' }}</h1>
        <p>{{ $editing ? 'Perbarui nama dan kode kategori. Periksa dampaknya pada aset sebelum menyimpan.' : 'Buat kelompok aset dengan nama yang mudah dikenali dan kode tag yang unik.' }}</p>
    </div>
    <div class="category-panel">
        <div class="category-panel-header"><h2 class="text-sm font-semibold text-gray-900">Informasi kategori</h2><span>Semua kolom wajib diisi</span></div>
        <form action="{{ $editing ? route('categories.update', $category->id) : route('categories.store') }}" method="POST" class="category-form">
            @csrf
            @if($editing) @method('PUT') @endif
            @if($errors->any())
            <div class="category-form-notice category-form-error" role="alert"><strong>Periksa kembali isian berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @if($editing && $category->assets_count > 0)
            <div class="category-form-notice"><strong>Digunakan oleh {{ $category->assets_count }} aset.</strong><p>Perubahan kode kategori juga memperbarui kategori pada aset terkait setelah perubahan diterapkan.</p></div>
            @endif
            @if($editing && auth()->user()->role !== 'super_admin')
            <p class="category-approval-note">Perubahan akan dikirim untuk persetujuan Super Admin.</p>
            @endif
            <div class="category-form-field">
                <label for="name">Nama kategori <span aria-hidden="true">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $category->name ?? '') }}" required maxlength="100" placeholder="Contoh: Peralatan kantor" aria-describedby="name-help" @error('name') aria-invalid="true" @enderror>
                <p id="name-help">Nama yang ditampilkan pada daftar dan detail aset.</p>
                @error('name')<p class="category-field-error">{{ $message }}</p>@enderror
            </div>
            <div class="category-form-grid">
                <div class="category-form-field">
                    <label for="slug">Kode kategori <span aria-hidden="true">*</span></label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $category->slug ?? '') }}" required maxlength="50" placeholder="Contoh: peralatan-kantor" aria-describedby="slug-help" @error('slug') aria-invalid="true" @enderror>
                    <p id="slug-help">Terisi dari nama kategori dan dapat diedit. Gunakan huruf, angka, tanda hubung, atau garis bawah. Kode harus unik.</p>
                    @error('slug')<p class="category-field-error">{{ $message }}</p>@enderror
                </div>
                <div class="category-form-field">
                    <label for="prefix">Awalan kode tag aset <span aria-hidden="true">*</span></label>
                    <input type="text" name="prefix" id="prefix" value="{{ old('prefix', $category->prefix ?? '') }}" required maxlength="5" placeholder="Contoh: PK" aria-describedby="prefix-help" @error('prefix') aria-invalid="true" @enderror>
                    <p id="prefix-help">Maksimal 5 karakter dan berbeda dari kategori lain. {{ $editing ? 'Perubahan hanya berlaku untuk tag aset baru.' : 'Digunakan pada tag aset yang dibuat dalam kategori ini.' }}</p>
                    @error('prefix')<p class="category-field-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="category-tag-preview"><span>Contoh tag untuk aset baru</span><strong><span id="category-tag-prefix">{{ old('prefix', $category->prefix ?? '') ?: 'PK' }}</span>-{{ $categoryYear }}-001</strong><p>Nomor urut dibuat otomatis saat menambah aset.</p></div>
            <div class="asset-form-actions">
                <button type="submit" class="category-action">{{ $editing ? (auth()->user()->role === 'super_admin' ? 'Simpan perubahan' : 'Kirim permintaan perubahan') : 'Simpan kategori' }}</button>
                <a href="{{ route('categories.index') }}" class="category-action">Batal</a>
            </div>
        </form>
    </div>
</div>

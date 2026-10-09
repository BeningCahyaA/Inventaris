<x-app-layout>
    <x-slot name="title">Tambah User</x-slot>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah User</h2></x-slot>
    <div class="user-create-page">
        <div class="user-create-container">
            <a href="{{ route('users.index') }}" class="user-form-back">← Kembali ke daftar user</a>
            <div class="management-intro"><div><h3>Tambah pengguna baru</h3><p>Lengkapi informasi akun dan tentukan hak akses pengguna.</p></div></div>
            <div class="management-panel">
                <div class="management-panel-heading"><h3>Informasi pengguna</h3><span>Semua kolom wajib diisi</span></div>
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="user-form-fields">
                        <div class="user-form-grid">
                            <div class="user-form-field">
                                <label for="name">Nama pengguna <span>*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" placeholder="Masukkan nama pengguna" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                                @error('name') <p id="name-error" class="user-form-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="user-form-field">
                                <label for="email">Email <span>*</span></label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" placeholder="nama@perusahaan.com" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                                @error('email') <p id="email-error" class="user-form-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <fieldset class="user-form-section">
                            <legend>Keamanan akun</legend>
                            <p class="user-form-help">Buat kata sandi dan masukkan kembali untuk konfirmasi.</p>
                            <div class="user-form-grid">
                                <div class="user-form-field">
                                    <label for="password">Kata sandi <span>*</span></label>
                                    <input type="password" name="password" id="password" required autocomplete="new-password" placeholder="Masukkan kata sandi" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                                    @error('password') <p id="password-error" class="user-form-error">{{ $message }}</p> @enderror
                                </div>
                                <div class="user-form-field">
                                    <label for="password_confirmation">Konfirmasi kata sandi <span>*</span></label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi" @error('password_confirmation') aria-invalid="true" aria-describedby="confirmation-error" @enderror>
                                    @error('password_confirmation') <p id="confirmation-error" class="user-form-error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </fieldset>
                        <div class="user-form-section user-form-field">
                            <label for="role">Hak akses <span>*</span></label>
                            <select name="role" id="role" required aria-describedby="role-help{{ $errors->has('role') ? ' role-error' : '' }}" @error('role') aria-invalid="true" @enderror>
                                <option value="" disabled @selected(!old('role'))>Pilih hak akses</option>
                                <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                                <option value="super_admin" @selected(old('role') === 'super_admin')>Super Admin</option>
                            </select>
                            <p id="role-help" class="user-form-help">Admin mengelola aset. Super Admin juga mengelola pengguna dan persetujuan.</p>
                            @error('role') <p id="role-error" class="user-form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="user-form-actions">
                        <a href="{{ route('users.index') }}" class="user-form-cancel">Batal</a>
                        <button type="submit" class="user-form-submit">Simpan pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

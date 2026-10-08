<x-app-layout><x-slot name="title">Edit Kategori</x-slot><x-slot name="header"><h2 class="font-semibold text-lg sm:text-xl text-gray-800">Edit Kategori</h2></x-slot>
    @include('categories.partials.form')
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const prefixInput = document.getElementById('prefix');
            const prefixPreview = document.getElementById('category-tag-prefix');
            prefixInput.addEventListener('input', () => {
                prefixPreview.textContent = prefixInput.value.trim() || 'PK';
            });
            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');
            const originalName = nameInput.value;
            const originalSlug = slugInput.value;
            let slugManuallyEdited = slugInput.value !== generateSlug(nameInput.value);

            function generateSlug(text) {
                return text
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
            }

            nameInput.addEventListener('input', function () {
                if (!slugManuallyEdited) {
                    if (this.value !== originalName) {
                        slugInput.value = generateSlug(this.value);
                    } else {
                        slugInput.value = originalSlug;
                    }
                }
            });

            slugInput.addEventListener('input', function () {
                slugManuallyEdited = this.value !== generateSlug(nameInput.value);
            });
        });
    </script>
    @endpush
</x-app-layout>

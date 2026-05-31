{{--
    Autocompletar del navegador rellena el DOM pero no dispara wire:model ni updatedDni.
    Sincroniza valores al componente Livewire del formulario de login y habilita el envío
    solo cuando Livewire ya está listo (evita POST nativo / 419 por carrera de sesión).
--}}
@script
<script>
    (() => {
        const form = $wire.$el.closest('form');
        if (!form) {
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        let livewireReady = false;

        const setSubmitEnabled = (enabled) => {
            if (!submitBtn) {
                return;
            }
            submitBtn.disabled = !enabled;
        };

        setSubmitEnabled(false);

        document.addEventListener('livewire:init', () => {
            livewireReady = true;
        }, { once: true });

        const readField = (id) => {
            const el = form.querySelector('#' + id);
            return el ? String(el.value || '') : '';
        };

        const syncAutofill = () => {
            const dniVal = readField('dni').replace(/\D/g, '').slice(0, 11);
            if (dniVal.length >= 7 && $wire.get('dni') !== dniVal) {
                $wire.set('dni', dniVal);
            }

            const pwrdVal = readField('pwrd');
            if (pwrdVal !== '' && $wire.get('pwrd') !== pwrdVal) {
                $wire.set('pwrd', pwrdVal);
            }

            if (livewireReady) {
                setSubmitEnabled(true);
            }
        };

        form.querySelector('#dni')?.addEventListener('change', syncAutofill);
        form.querySelector('#pwrd')?.addEventListener('change', syncAutofill);

        [50, 150, 400, 800].forEach((ms) => window.setTimeout(syncAutofill, ms));

        document.addEventListener('livewire:init', () => {
            syncAutofill();
            window.setTimeout(() => setSubmitEnabled(true), 100);
        }, { once: true });
    })();
</script>
@endscript

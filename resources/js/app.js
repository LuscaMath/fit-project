

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.performedSetForm = (config) => ({
    sets: config.sets ?? [],
    measurementType: config.measurementType,
    storeUrl: config.storeUrl,
    updateUrlTemplate: config.updateUrlTemplate,
    destroyUrlTemplate: config.destroyUrlTemplate,
    saving: false,
    busySetId: null,
    error: '',
    status: '',
    form: {
        set_number: config.nextSetNumber ?? 1,
        repetitions: '',
        duration_seconds: '',
        load_kg: '',
        client_request_id: '',
    },

    csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    },

    newRequestId() {
        if (window.crypto?.randomUUID) {
            return window.crypto.randomUUID();
        }

        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (character) => {
            const random = Math.random() * 16 | 0;
            const value = character === 'x' ? random : (random & 0x3 | 0x8);

            return value.toString(16);
        });
    },

    async request(url, method, payload = null) {
        const headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.csrfToken(),
        };

        const options = { method, headers };

        if (payload !== null) {
            headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(payload);
        }

        const response = await fetch(url, options);
        const body = await response.json().catch(() => ({}));

        if (!response.ok) {
            const validationErrors = body.errors ? Object.values(body.errors).flat().join(' ') : '';
            throw new Error(validationErrors || body.message || 'Nao foi possivel concluir a operacao.');
        }

        return body;
    },

    async submit() {
        this.error = '';
        this.status = '';
        this.saving = true;

        if (!this.form.client_request_id) {
            this.form.client_request_id = this.newRequestId();
        }

        try {
            const response = await this.request(this.storeUrl, 'POST', this.form);
            const performedSet = response.data;

            if (!this.sets.some((set) => set.id === performedSet.id)) {
                this.sets.push({ ...performedSet, editing: false });
                this.sets.sort((first, second) => first.set_number - second.set_number);
            }

            this.form = {
                set_number: Math.max(...this.sets.map((set) => set.set_number), 0) + 1,
                repetitions: '',
                duration_seconds: '',
                load_kg: '',
                client_request_id: '',
            };
            this.status = response.message;
        } catch (error) {
            this.error = error.message;
        } finally {
            this.saving = false;
        }
    },

    beginEdit(set) {
        set.original = { ...set };
        set.editing = true;
        this.error = '';
        this.status = '';
    },

    cancelEdit(set) {
        Object.assign(set, set.original ?? {});
        delete set.original;
        set.editing = false;
    },

    async update(set) {
        this.error = '';
        this.status = '';
        this.busySetId = set.id;

        try {
            const response = await this.request(
                this.updateUrlTemplate.replace('__SET__', set.id),
                'PATCH',
                {
                    repetitions: set.repetitions || null,
                    duration_seconds: set.duration_seconds || null,
                    load_kg: set.load_kg === '' ? null : set.load_kg,
                },
            );

            Object.assign(set, response.data, { editing: false });
            delete set.original;
            this.status = response.message;
        } catch (error) {
            this.error = error.message;
        } finally {
            this.busySetId = null;
        }
    },

    async remove(set) {
        if (! window.confirm('Excluir esta serie?')) {
            return;
        }

        this.error = '';
        this.status = '';
        this.busySetId = set.id;

        try {
            const response = await this.request(
                this.destroyUrlTemplate.replace('__SET__', set.id),
                'DELETE',
            );
            this.sets = this.sets.filter((item) => item.id !== set.id);
            this.status = response.message;
        } catch (error) {
            this.error = error.message;
        } finally {
            this.busySetId = null;
        }
    },
});

Alpine.start();

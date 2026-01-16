export const FormUtils = {

    init() {
        this.initAddRow();
        this.initRemoveRow();
        this.formatMoneyInputs();
    },

    initAddRow() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-add-row');
            if (!btn) return;

            const table = document.querySelector(btn.dataset.table);
            if (!table) return;

            this.duplicateEntry(table);
        });
    },

    duplicateEntry(tableOrSelector) {
        const table = typeof tableOrSelector === 'string'
            ? document.querySelector(tableOrSelector)
            : tableOrSelector;

        if (!table || !(table instanceof HTMLElement)) return;

        const tbody = table.querySelector('.js-entry');
        if (!tbody) return;

        const rows = tbody.querySelectorAll('tr');
        const templateRow = rows[rows.length - 1];
        const newIndex = rows.length;

        const clone = templateRow.cloneNode(true);
        clone.dataset.index = newIndex;

        clone.querySelectorAll('input, select, textarea').forEach(el => {

            // Reset
            if (el.tagName === 'SELECT') el.selectedIndex = 0;
            else el.value = '';

            // Update name
            if (el.name) {
                el.name = el.name.replace(/\[\d+]/, `[${newIndex}]`);
            }

            // Money inputs
            if (el.classList.contains('money-input')) {
                const hidden = el.nextElementSibling;
                if (hidden?.classList.contains('hidden-money')) {
                    hidden.name = el.dataset.hiddenName
                        .replace(/\[\d+]/, `[${newIndex}]`);
                    hidden.value = '';
                }
            }
        });

        tbody.appendChild(clone);
    },

    initRemoveRow() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-remove-row');
            if (!btn) return;

            const row = btn.closest('tr');
            const tbody = row.closest('.js-entry');
            if (!row || !tbody) return;

            if (tbody.children.length === 1) return;

            row.remove();
            this.reindexRows(tbody);
        });
    },

    reindexRows(tbody) {
        tbody.querySelectorAll('tr').forEach((row, index) => {
            row.dataset.index = index;

            row.querySelectorAll('input, select, textarea').forEach(el => {
                if (el.name) {
                    el.name = el.name.replace(/\[\d+]/, `[${index}]`);
                }

                if (el.classList.contains('money-input')) {
                    const hidden = el.nextElementSibling;
                    if (hidden?.classList.contains('hidden-money')) {
                        hidden.name = el.dataset.hiddenName
                            .replace(/\[\d+]/, `[${index}]`);
                    }
                }
            });
        });
    },

    formatMoneyInputs(container = document) {
        container.addEventListener('input', (e) => {
            if (!e.target.classList.contains('money-input')) return;

            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value
                ? '$ ' + parseInt(value).toLocaleString('es-CO')
                : '';

            const hidden = e.target.nextElementSibling;
            if (hidden?.classList.contains('hidden-money')) {
                hidden.value = value;
            }
        });

        container.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => {
                form.querySelectorAll('.money-input').forEach(input => {
                    const hidden = input.nextElementSibling;
                    if (hidden?.classList.contains('hidden-money')) {
                        hidden.value = input.value.replace(/\D/g, '');
                    }
                });
            });
        });
    }
};

/**
 * Inicializar las utilidades cuando el DOM complete la carga
*/
document.addEventListener('DOMContentLoaded', () => {
    FormUtils.init();
});
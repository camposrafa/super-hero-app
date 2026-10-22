document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-range-input]').forEach((input) => {
        const output = document.querySelector(
            `[data-range-output="${input.dataset.rangeInput}"]`,
        );

        if (!output) {
            return;
        }

        const updateOutput = () => {
            output.value = input.value;
            output.textContent = input.value;
        };

        input.addEventListener('input', updateOutput);
        updateOutput();
    });

    document.querySelectorAll('[data-coming-soon]').forEach((button) => {
        button.addEventListener('click', () => {
            window.alert('A exploração de anomalias será adicionada em uma próxima etapa.');
        });
    });

    document.querySelectorAll('[data-hero-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            const submitButton = form.querySelector('button[type="submit"]');

            if (!submitButton) {
                return;
            }

            submitButton.textContent = 'Criando sua anomalia...';
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
        });
    });
});

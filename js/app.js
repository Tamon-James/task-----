document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-copy-target]').forEach((button) => {
        button.addEventListener('click', async () => {
            const target = document.querySelector(button.dataset.copyTarget);
            if (!target) {
                return;
            }

            try {
                await navigator.clipboard.writeText(target.value || target.textContent);
                button.textContent = 'コピーしました';
                setTimeout(() => {
                    button.textContent = 'コピー';
                }, 1500);
            } catch (error) {
                target.select?.();
                document.execCommand('copy');
                button.textContent = 'コピーしました';
            }
        });
    });
});


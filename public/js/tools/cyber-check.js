document.addEventListener('DOMContentLoaded', function () {

    const resultsPanel = document.getElementById('check-results');
    const resultSummary = document.getElementById('result-summary');
    const resultMessage = document.getElementById('result-message');

    /**
     * Run a cybersecurity check (Home or Business)
     */
    function runCheck({ formId, questionPrefix, submitId }) {

        const form = document.getElementById(formId);
        const button = document.getElementById(submitId);

        if (!form || !button) return;

        button.addEventListener('click', function () {

            let totalScore = 0;
            const totalQuestions = 5;

            for (let i = 1; i <= totalQuestions; i++) {
                const selected = document.querySelector(
                    `input[name="${questionPrefix}${i}"]:checked`
                );

                if (!selected) {
                    alert('Please answer all questions before seeing your results.');
                    return;
                }

                totalScore += parseInt(selected.dataset.score, 10);
            }

            let headline = '';
            let explanation = '';

            if (totalScore <= 4) {
                headline = 'Your cybersecurity needs attention';
                explanation =
                    'There are gaps that could put your data or systems at risk. The good news is that most of these issues are straightforward to improve.';
            } else if (totalScore <= 7) {
                headline = 'You’re doing okay, but there’s room to improve';
                explanation =
                    'You have some good habits in place, but tightening a few areas would significantly reduce risk.';
            } else {
                headline = 'You’re in good shape';
                explanation =
                    'You’re doing many of the right things already. A quick review could help make sure nothing is being missed.';
            }

            // Update results
            resultSummary.innerHTML = `<strong>Score:</strong> ${totalScore} / 10`;
            resultMessage.innerHTML = `<strong>${headline}</strong><br>${explanation}`;

            // Hide active form, show results
            form.classList.add('hidden');
            resultsPanel.classList.remove('hidden');
            resultsPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    /**
     * Initialise both checks
     */
    runCheck({
        formId: 'home-check',
        questionPrefix: 'q',
        submitId: 'check-submit'
    });

    runCheck({
        formId: 'business-check',
        questionPrefix: 'bq',
        submitId: 'business-submit'
    });

    /**
     * Toggle Home / Business selector
     */
    document.querySelectorAll('.check-selector button').forEach(button => {
        button.addEventListener('click', () => {

            if (button.disabled) return;

            // Toggle active button
            document.querySelectorAll('.check-selector button')
                .forEach(b => b.classList.remove('active'));
            button.classList.add('active');

            // Hide all forms + results
            document.querySelectorAll('.check-form')
                .forEach(form => form.classList.add('hidden'));

            resultsPanel.classList.add('hidden');

            // Show selected form
            const type = button.dataset.check;
            if (type === 'home') {
                document.getElementById('home-check').classList.remove('hidden');
            }
            if (type === 'business') {
                document.getElementById('business-check').classList.remove('hidden');
            }
        });
    });

});

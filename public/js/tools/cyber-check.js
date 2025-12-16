document.addEventListener('DOMContentLoaded', () => {

    const button = document.getElementById('check-submit');
    const form = document.getElementById('home-check');
    const results = document.getElementById('check-results');

    if (!button || !form || !results) return;

    button.addEventListener('click', () => {

        let totalScore = 0;

        for (let i = 1; i <= 5; i++) {
            const selected = document.querySelector(`input[name="q${i}"]:checked`);
            if (!selected) {
                alert('Please answer all questions.');
                return;
            }
            totalScore += Number(selected.dataset.score);
        }

        let headline, explanation;

        if (totalScore <= 4) {
            headline = 'Your cybersecurity needs attention';
            explanation = 'There are a few gaps that could put your data at risk.';
        } else if (totalScore <= 7) {
            headline = 'You’re doing okay, but there’s room to improve';
            explanation = 'You have some good habits in place, but tightening a few areas would help.';
        } else {
            headline = 'You’re in good shape';
            explanation = 'You’re doing many of the right things already.';
        }

        document.getElementById('result-summary').innerHTML =
            `<strong>Score:</strong> ${totalScore} / 10`;

        document.getElementById('result-message').innerHTML =
            `<strong>${headline}</strong><br>${explanation}`;

        form.classList.add('hidden');
        results.classList.remove('hidden');
        results.scrollIntoView({ behavior: 'smooth' });
    });

});
document.addEventListener('DOMContentLoaded', () => {

    document.addEventListener('click', (event) => {

        const commentButton = event.target.closest('.comment-toggle');

        if (!commentButton) return;

        event.preventDefault();
        event.stopPropagation();

        const card = commentButton.closest('.gallery-card');

        if (!card) return;

        const section = card.querySelector('.comments-section');

        if (!section) return;

        section.hidden = !section.hidden;

        commentButton.setAttribute(
            'aria-expanded',
            String(!section.hidden)
        );

    });

});

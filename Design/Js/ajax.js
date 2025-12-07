document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', async e => {
        e.preventDefault();
        const postId = form.dataset.postId;
        const input = form.querySelector('input[name="comment"]');
        const comment = input.value;

        const res = await fetch('../service/add_comment.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `post_id=${postId}&comment=${encodeURIComponent(comment)}`
        });
        const data = await res.text();

        if(data === 'ok') {
            const commentsDiv = document.getElementById('comments-' + postId);
            const div = document.createElement('div');
            div.classList.add('text-gray-700','text-sm');
            div.innerHTML = `<span class="font-bold">Anda:</span> ${comment} <span class="text-gray-400 text-xs">(Baru saja)</span>`;
            commentsDiv.appendChild(div);
            input.value = '';
            commentsDiv.scrollIntoView({behavior: 'smooth'});
        }
    });
});

'use strict'

let form = document.querySelector('#form');
let url = '/server.php';

form.addEventListener('submit', async function(e) {
    e.preventDefault();
    let response = await fetch(url, {
        method: 'POST',
        body: new FormData(form)
    });
    let result = await response.text();
    form.reset();
});




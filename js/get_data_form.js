$("#formPindahKamar").on('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
});

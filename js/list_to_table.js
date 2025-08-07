$(function () {
    const thPricingTable = $("#pricingTable > thead");
    const tbPricingTable = $("#pricingTable > tbody");

    function get_list_tipe_kamar() {
        $.get("/api/web/kamar.php", { action: "list_tipe_kamar" })
            .fail(failHandler)
            .done(res => {
                if (res.status == false) {
                    alert(res.msg || "failed to fetch data kamar");
                    return;
                }
                if (!Array.isArray(res.data)) {
                    alert("Invalid data kamar");
                    return;
                }
                const data = res.data;
                const head = Object.keys(data[0]);
                console.log(data);
                thPricingTable.html(be("tr", {}, head.map(h => be("th", {}, [h.replaceAll('_', " ")]))));
                tbPricingTable.html(data.map(d => be("tr", {}, head.map(h => be("td", {}, [d[h]])))));
            });
    }
    get_list_tipe_kamar();
});

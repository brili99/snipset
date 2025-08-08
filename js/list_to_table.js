$(function () {
    const thPricingTable = $("#pricingTable > thead");
    const tbPricingTable = $("#pricingTable > tbody");

    function update_kamar_data(roomType, field, value) {
        $.post("/api/web/kamar.php", {
            action: "update_kamar_data",
            token: localStorage.getItem('token'),
            roomType, field, value
        }).done(res => {
            if (res.status == false) {
                alert(res.msg || "Failed to update data");
                return;
            }
        });
    }

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
                const primaryKey = "roomType";
                console.log(data);
                thPricingTable.html(be("tr", {}, head.map(h => be("th", {}, [h.replaceAll('_', " ")]))));
                tbPricingTable.html(data.map(d => be("tr", {}, head.map(h => be("td", {
                    contenteditable: h != primaryKey ? "true" : "false", title: "Click to edit"
                }, [d[h]], {
                    focusout: (e) => update_kamar_data(d[primaryKey], h, e.target.innerText)
                })))));
            });
    }
    get_list_tipe_kamar();
});

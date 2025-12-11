async function loadChart(company) {
    const res = await fetch("chart_data.php?symbol=" + company);
    const data = await res.json();

    const ctx = document.getElementById("priceChart").getContext("2d");
    
    if (window.currentChart) {
        window.currentChart.destroy();
    }

    window.currentChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: data.dates,
            datasets: [{
                label: company + " Price",
                data: data.prices,
                borderWidth: 2
            }]
        }
    });
}

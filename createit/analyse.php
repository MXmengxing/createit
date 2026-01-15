<?php include "navbar.php"; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Interactieve Analyse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <script>
        const savedTheme = localStorage.getItem("theme") || "light";
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", () => {
                document.body.className = savedTheme;
            });
        } else {
            document.body.className = savedTheme;
        }
    </script>
    <script src="theme.js"></script>
</head>

<body>

<header>Interactieve Analyse</header>

<main>
    <div class="page-title">
        <h1>Analyse &amp; Scenario Simulatie</h1>
        <p>Pas cijfers aan en bekijk voorspellingen per bedrijf</p>
    </div>

    <!-- Bedrijf选择 + 说明 -->
    <div class="card selector-card">
        <label for="companySelect"><strong>Selecteer een bedrijf</strong></label>
        <select id="companySelect">
            <option value="AAPL">Apple (AAPL)</option>
            <option value="MSFT">Microsoft (MSFT)</option>
            <option value="AMZN">Amazon (AMZN)</option>
            <option value="TSLA">Tesla (TSLA)</option>
            <option value="NVDA">Nvidia (NVDA)</option>
        </select>
        <small>Jaar 1 cijfers worden automatisch via de API geladen.</small>
    </div>

    <div class="container analyse-cards">

        <!-- Jaar 1：只读，来自 API -->
        <div class="card analyse-card">
            <h3>Jaar 1 (API data)</h3>

            <div class="row-line">
                <label for="y1rev">Revenue</label>
                <input id="y1rev" type="number" disabled>
            </div>

            <div class="row-line">
                <label for="y1cost">Cost of Revenue</label>
                <input id="y1cost" type="number" disabled>
            </div>

            <div class="row-line">
                <label for="y1net">Net Income</label>
                <input id="y1net" type="number" disabled>
            </div>
        </div>

        <!-- Jaar 2：用户改 revenue/cost，净利润自动 -->
        <div class="card analyse-card">
            <h3>Jaar 2 (scenario)</h3>

            <div class="row-line">
                <label for="y2rev">Revenue</label>
                <input id="y2rev" type="number">
            </div>

            <div class="row-line">
                <label for="y2cost">Cost</label>
                <input id="y2cost" type="number">
            </div>

            <div class="row-line">
                <label for="y2net">Net Income (auto)</label>
                <input id="y2net" type="number" disabled>
            </div>
        </div>

        <!-- Jaar 3：同上 -->
        <div class="card analyse-card">
            <h3>Jaar 3 (scenario)</h3>

            <div class="row-line">
                <label for="y3rev">Revenue</label>
                <input id="y3rev" type="number">
            </div>

            <div class="row-line">
                <label for="y3cost">Cost</label>
                <input id="y3cost" type="number">
            </div>

            <div class="row-line">
                <label for="y3net">Net Income (auto)</label>
                <input id="y3net" type="number" disabled>
            </div>
        </div>

    </div>
</main>

<script>
// 载入 Jaar 1（API Data）
async function loadYear1() {
    const symbol = document.getElementById("companySelect").value;

    try {
        const res = await fetch("analysis_api.php?symbol=" + encodeURIComponent(symbol));
        const data = await res.json();

        if (data.error) {
            alert("API fout: " + (data.msg || "Onbekende fout"));
            return;
        }

        // 填 Jaar 1
        document.getElementById("y1rev").value  = data.revenue ?? 0;
        document.getElementById("y1cost").value = data.cost ?? 0;
        document.getElementById("y1net").value  = data.net ?? 0;

        // 给 Jaar 2 / 3 一个初始 scenario（简单：收入增加 5% / 10%，成本比例相同）
        const y1rev  = Number(data.revenue || 0);
        const y1cost = Number(data.cost || 0);
        const costRatio = y1rev ? y1cost / y1rev : 0.6;

        const y2rev = y1rev * 1.05;
        const y3rev = y1rev * 1.10;

        document.getElementById("y2rev").value = y2rev.toFixed(0);
        document.getElementById("y3rev").value = y3rev.toFixed(0);

        document.getElementById("y2cost").value = (y2rev * costRatio).toFixed(0);
        document.getElementById("y3cost").value = (y3rev * costRatio).toFixed(0);

        recalcScenario();
    } catch (e) {
        console.error(e);
        alert("API fout bij laden van Jaar 1");
    }
}

// 根据 Jaar 2 / 3 的 Revenue & Cost 自动算 Net Income
function recalcScenario() {
    const y2rev  = Number(document.getElementById("y2rev").value || 0);
    const y2cost = Number(document.getElementById("y2cost").value || 0);
    const y3rev  = Number(document.getElementById("y3rev").value || 0);
    const y3cost = Number(document.getElementById("y3cost").value || 0);

    document.getElementById("y2net").value = (y2rev - y2cost).toFixed(0);
    document.getElementById("y3net").value = (y3rev - y3cost).toFixed(0);
}

// 监听用户输入，实时更新 Net Income
["y2rev","y2cost","y3rev","y3cost"].forEach(id => {
    const el = document.getElementById(id);
    el.addEventListener("input", recalcScenario);
});

// 切换公司时重新加载 Jaar 1
document.getElementById("companySelect").addEventListener("change", loadYear1);

// 初次加载（默认 Apple）
loadYear1();
</script>

</body>
</html>

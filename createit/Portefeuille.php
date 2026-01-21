<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Portefeuille</title>
    <link rel="stylesheet" href="style.css">

    <script>
        const savedTheme = localStorage.getItem("theme") || "light";
        document.documentElement.style.backgroundColor = savedTheme === "dark" ? "#000000" : "#ffffff";

        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", () => {
                document.body.className = savedTheme;
            });
        } else {
            document.body.className = savedTheme;
        }
    </script>

    <script src="theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<?php include "navbar.php"; ?>

<header>Portefeuille</header>

<main>
    <div class="page-title">
        <h1>Jouw Portefeuille</h1>
        <p>Overzicht van posities & totale waarde</p>
    </div>

    <div class="portfolio-summary">
        <div class="summary-card card">
            <h3>Total Portfolio Value</h3>
            <div class="value" id="totalValue">Laden...</div>
        </div>
    </div>

    <div class="chart-section card">
        <h3>Portfolio Verdeling</h3>

        <div class="chart-container">
            <canvas id="portfolioChart" height="220"></canvas>
        </div>
    </div>
</main>

<script>
let chartInstance = null;

// Demo: 每个股票 10 股（你以后可以改成真实持仓）
const SHARES_PER_SYMBOL = 10;

async function loadPortfolio() {
    try {
        const res = await fetch("intraday_data.php", { cache: "no-store" });
        if (!res.ok) throw new Error("HTTP " + res.status);

        const data = await res.json();
        const list = data.symbols || [];

        // 计算总价值 + 准备图表数据
        let totalValue = 0;
        const labels = [];
        const values = [];

        list.forEach(item => {
            const symbol = item.symbol;
            const price = Number(item.price) || 0;
            const positionValue = price * SHARES_PER_SYMBOL;

            labels.push(symbol);
            values.push(positionValue);
            totalValue += positionValue;
        });

        // 显示总资产
        document.getElementById("totalValue").innerText = "€" + totalValue.toFixed(2);

        // 画图
        const canvas = document.getElementById("portfolioChart");
        if (!canvas) return;

        // 防止重复创建
        if (chartInstance) chartInstance.destroy();

        chartInstance = new Chart(canvas, {
            type: "pie",
            data: {
                labels: labels,
                datasets: [{
                    data: values
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: "right"
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw || 0;
                                const pct = totalValue > 0 ? (value / totalValue * 100) : 0;
                                return `${context.label}: €${value.toFixed(2)} (${pct.toFixed(1)}%)`;
                            }
                        }
                    }
                }
            }
        });

    } catch (e) {
        console.error("Error loading portfolio:", e);
        document.getElementById("totalValue").innerText = "Error loading data";

        // 如果图表也需要提示
        const canvas = document.getElementById("portfolioChart");
        if (canvas) {
            const ctx = canvas.getContext("2d");
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.font = "16px Arial";
            ctx.fillText("Kan grafiek niet laden", 10, 30);
        }
    }
}

loadPortfolio();
</script>

</body>
</html>

let revenueChart = null;
let vehicleChart = null;

const btnToday = document.getElementById("btnToday");
const btnWeek = document.getElementById("btnWeek");
const btnMonth = document.getElementById("btnMonth");
const btnYear = document.getElementById("btnYear");

function activeButton(btn)
{
    [btnToday, btnWeek, btnMonth, btnYear].forEach(b => {
        b.classList.remove("btn-amber");
        b.classList.add("btn-outline-light");
    });

    btn.classList.remove("btn-outline-light");
    btn.classList.add("btn-amber");
}

async function loadRevenue(type)
{
    const response = await fetch("/msp/ajax/get_revenue.php?type=" + type);
    const data = await response.json();

    const labels = [];
    const revenue = [];

    data.forEach(row => {
        labels.push(row.label);
        revenue.push(parseFloat(row.revenue));
    });

    if(revenueChart)
    {
        revenueChart.destroy();
    }

    revenueChart = new Chart(document.getElementById("revenueChart"), {

        type:"line",

        data:{
            labels:labels,

            datasets:[{
                label:"Revenue",
                data:revenue,
                borderColor:"#F2A93B",
                backgroundColor:"rgba(242,169,59,.18)",
                borderWidth:3,
                fill:true,
                tension:.35
            }]
        },

        options:{

            responsive:true,
            maintainAspectRatio:false,

            plugins:{
                legend:{
                    labels:{
                        color:"#E8ECF1"
                    }
                }
            },

            scales:{
                x:{
                    ticks:{color:"#93A0B4"},
                    grid:{color:"#2A313D"}
                },
                y:{
                    beginAtZero:true,
                    ticks:{color:"#93A0B4"},
                    grid:{color:"#2A313D"}
                }
            }

        }

    });

}

async function loadVehicleRevenue()
{
    const response = await fetch("/msp/ajax/get_vehicle_revenue.php");
    const data = await response.json();

    const labels = [];
    const revenue = [];

    data.forEach(row=>{

        labels.push(row.vehicle_type);
        revenue.push(parseFloat(row.revenue));

    });

    if(vehicleChart)
    {
        vehicleChart.destroy();
    }

    vehicleChart = new Chart(document.getElementById("vehicleRevenueChart"),{

        type:"pie",

        data:{

            labels:labels,

            datasets:[{

                data:revenue,

                backgroundColor:[
                    "#F2A93B",
                    "#34C77B",
                    "#E5484D"
                ],

                borderColor:"#171B21",
                borderWidth:2

            }]

        },

        options:{

            responsive:true,

            maintainAspectRatio:false,

            plugins:{

                legend:{
                    position:"bottom",

                    labels:{
                        color:"#E8ECF1",
                        padding:20
                    }
                }

            }

        }

    });

}


async function loadPaymentAnalytics()
{
    const response = await fetch("/msp/ajax/get_payment_analytics.php");
    const data = await response.json();

    const labels = [];
    const revenue = [];

    data.forEach(row => {

        labels.push(row.payment_mode);
        revenue.push(parseFloat(row.revenue));

    });

    new Chart(document.getElementById("paymentChart"),{

        type:"doughnut",

        data:{

            labels:labels,

            datasets:[{

                data:revenue,

                backgroundColor:[
                    "#34C77B",
                    "#F2A93B",
                    "#4DA3FF"
                ],

                borderColor:"#171B21",
                borderWidth:2

            }]

        },

        options:{

            responsive:true,

            maintainAspectRatio:false,

            plugins:{

                legend:{

                    position:"bottom",

                    labels:{
                        color:"#E8ECF1"
                    }

                }

            }

        }

    });

}

let floorChart = null;

let dateRevChart = null;

async function loadDateRevenue(dateStr)
{
    const summaryRes = await fetch("/msp/ajax/get_date_summary.php?date=" + encodeURIComponent(dateStr));
    const summary = await summaryRes.json();

    if (!summary.ok) { return; }

    document.getElementById("dateRevTotal").textContent = "₹" + Number(summary.total_revenue).toLocaleString("en-IN", {minimumFractionDigits:2, maximumFractionDigits:2});
    document.getElementById("dateRevVehicles").textContent = summary.vehicles_exited;
    document.getElementById("dateRevAvg").textContent = "₹" + Number(summary.avg_charge).toLocaleString("en-IN", {minimumFractionDigits:2, maximumFractionDigits:2});

    const floorBody = document.getElementById("dateRevByFloor");
    floorBody.innerHTML = "";
    summary.by_floor.forEach(f => {
        const tr = document.createElement("tr");
        tr.innerHTML = "<td>" + f.floor_name + "</td><td>₹" + Number(f.revenue).toLocaleString("en-IN", {minimumFractionDigits:2}) + "</td><td>" + f.vehicles + "</td>";
        floorBody.appendChild(tr);
    });
    if (!summary.by_floor.length) {
        floorBody.innerHTML = '<tr><td colspan="3" class="text-secondary text-center py-3">No floors configured.</td></tr>';
    }

    const typeBody = document.getElementById("dateRevByType");
    typeBody.innerHTML = "";
    summary.by_type.forEach(t => {
        const tr = document.createElement("tr");
        tr.innerHTML = "<td>" + t.vehicle_type + "</td><td>₹" + Number(t.revenue).toLocaleString("en-IN", {minimumFractionDigits:2}) + "</td><td>" + t.vehicles + "</td>";
        typeBody.appendChild(tr);
    });
    if (!summary.by_type.length) {
        typeBody.innerHTML = '<tr><td colspan="3" class="text-secondary text-center py-3">No vehicles exited on this date.</td></tr>';
    }

    // hourly breakdown chart for the same date
    const hourlyRes = await fetch("/msp/ajax/get_revenue.php?type=date&date=" + encodeURIComponent(dateStr));
    const hourly = await hourlyRes.json();

    const labels = [];
    const revenue = [];
    for (let h = 0; h < 24; h++) {
        const row = hourly.find(r => parseInt(r.label) === h);
        labels.push(h + ":00");
        revenue.push(row ? parseFloat(row.revenue) : 0);
    }

    if (dateRevChart) { dateRevChart.destroy(); }

    dateRevChart = new Chart(document.getElementById("dateRevChart"), {
        type: "bar",
        data: {
            labels: labels,
            datasets: [{
                label: "Revenue (₹) — " + dateStr,
                data: revenue,
                backgroundColor: "#F2A93B",
                borderColor: "#F2A93B",
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: "#E8ECF1" } }
            },
            scales: {
                x: { ticks: { color: "#93A0B4" }, grid: { color: "#2A313D" } },
                y: { beginAtZero: true, ticks: { color: "#93A0B4" }, grid: { color: "#2A313D" } }
            }
        }
    });
}

const btnDateRev = document.getElementById("btnDateRev");
const dateRevPicker = document.getElementById("dateRevPicker");
if (btnDateRev && dateRevPicker) {
    btnDateRev.onclick = function () { loadDateRevenue(dateRevPicker.value); };
    // load today's data by default on page load
    loadDateRevenue(dateRevPicker.value);
}

async function loadFloorRevenue()
{
    const response = await fetch("/msp/ajax/get_floor_revenue.php");
    const data = await response.json();

    const labels = [];
    const revenue = [];

    data.forEach(row => {

        labels.push(row.floor_name);
        revenue.push(parseFloat(row.revenue));

    });

    if(floorChart)
    {
        floorChart.destroy();
    }

    floorChart = new Chart(document.getElementById("floorRevenueChart"),{

        type:"bar",

        data:{

            labels:labels,

            datasets:[{

                label:"Revenue",

                data:revenue,

                backgroundColor:"#F2A93B",
                borderColor:"#F2A93B",
                borderWidth:1

            }]

        },

        options:{

            responsive:true,

            maintainAspectRatio:false,

            plugins:{
                legend:{
                    labels:{
                        color:"#E8ECF1"
                    }
                }
            },

            scales:{

                x:{
                    ticks:{color:"#E8ECF1"},
                    grid:{color:"#2A313D"}
                },

                y:{
                    beginAtZero:true,
                    ticks:{color:"#E8ECF1"},
                    grid:{color:"#2A313D"}
                }

            }

        }

    });

}

btnToday.onclick=function(){
    activeButton(btnToday);
    loadRevenue("today");
};

btnWeek.onclick=function(){
    activeButton(btnWeek);
    loadRevenue("week");
};

btnMonth.onclick=function(){
    activeButton(btnMonth);
    loadRevenue("month");
};

btnYear.onclick=function(){
    activeButton(btnYear);
    loadRevenue("year");
};

loadRevenue("today");
loadVehicleRevenue();
loadPaymentAnalytics();
loadFloorRevenue();
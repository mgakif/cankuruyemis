<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form 10 İstatistikleri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col">
                <h1>Form 10 İstatistikleri</h1>
            </div>
            <div class="col text-end">
                <a href="{{ route('form-submissions.form10') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Geri Dön
                </a>
            </div>
        </div>

        {{-- Genel İstatistikler --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Toplam Kayıt</h5>
                        <h2 class="text-primary">{{ $total }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Cevaplanan</h5>
                        <h2 class="text-success">{{ $answered }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Bekleyen</h5>
                        <h2 class="text-warning">{{ $notAnswered }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Son 7 Gün</h5>
                        <h2 class="text-info">{{ $last7Days }}</h2>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grafikler --}}
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Ürün Dağılımı</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="urunChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>İl Dağılımı (Top 10)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="ilChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tablo verileri --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Ürün Detayları</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th>Adet</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($urunDagilim as $urun => $adet)
                                    <tr>
                                        <td>{{ $urun }}</td>
                                        <td>{{ $adet }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>İl Detayları</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>İl</th>
                                    <th>Adet</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ilDagilim->sortDesc()->take(15) as $il => $adet)
                                    <tr>
                                        <td>{{ $il }}</td>
                                        <td>{{ $adet }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Ürün Grafiği
        const urunCtx = document.getElementById('urunChart').getContext('2d');
        const urunChart = new Chart(urunCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($urunDagilim->keys()) !!},
                datasets: [{
                    data: {!! json_encode($urunDagilim->values()) !!},
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // İl Grafiği (Top 10)
        const ilCtx = document.getElementById('ilChart').getContext('2d');
        const topIller = {!! json_encode($ilDagilim->sortDesc()->take(10)) !!};
        const ilChart = new Chart(ilCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(topIller),
                datasets: [{
                    label: 'Başvuru Sayısı',
                    data: Object.values(topIller),
                    backgroundColor: '#36A2EB'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

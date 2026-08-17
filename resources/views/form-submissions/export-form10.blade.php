<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form 10 Dışa Aktar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col">
                <h1>Form 10 Dışa Aktar</h1>
            </div>
            <div class="col text-end">
                <a href="{{ route('form-submissions.form10') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Geri Dön
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>CSV Formatında İndir</h5>
                    </div>
                    <div class="card-body">
                        <p>Tüm form kayıtlarını CSV formatında indirin. Excel'de açılabilir.</p>
                        <a href="{{ route('form-submissions.form10.download-csv') }}" class="btn btn-success">
                            <i class="bi bi-download"></i> CSV İndir
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>JSON Formatında İndir</h5>
                    </div>
                    <div class="card-body">
                        <p>Tüm form kayıtlarını JSON formatında indirin. API entegrasyonları için ideal.</p>
                        <a href="{{ route('form-submissions.form10.export-json') }}" class="btn btn-primary" target="_blank">
                            <i class="bi bi-download"></i> JSON İndir
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Dışa Aktarma Hakkında</h5>
                    </div>
                    <div class="card-body">
                        <h6>CSV Formatı:</h6>
                        <ul>
                            <li>Microsoft Excel ve Google Sheets ile uyumlu</li>
                            <li>Türkçe karakter desteği (UTF-8 BOM)</li>
                            <li>Tüm form alanlarını içerir</li>
                        </ul>

                        <h6 class="mt-3">JSON Formatı:</h6>
                        <ul>
                            <li>API entegrasyonları için ideal</li>
                            <li>Programatik işlemler için uygun</li>
                            <li>Yapılandırılmış veri formatı</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

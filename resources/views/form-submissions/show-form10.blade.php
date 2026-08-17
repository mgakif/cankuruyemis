<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form 10 Kayıtları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col">
                <h1>Form 10 Kayıtları</h1>
            </div>
            <div class="col text-end">
                <a href="{{ route('form-submissions.form10.download-csv') }}" class="btn btn-success">
                    <i class="bi bi-download"></i> CSV İndir
                </a>
                <a href="{{ route('form-submissions.form10.stats') }}" class="btn btn-info">
                    <i class="bi bi-bar-chart"></i> İstatistikler
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ad Soyad</th>
                                <th>Email</th>
                                <th>Telefon</th>
                                <th>İl</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $submission)
                                @php
                                    $data = $submission->data ?? [];
                                @endphp
                                <tr>
                                    <td>{{ $submission->id }}</td>
                                    <td>{{ ($data['ad'] ?? '') . ' ' . ($data['soyad'] ?? '') }}</td>
                                    <td>{{ $data['email'] ?? '-' }}</td>
                                    <td>{{ $data['telefon'] ?? '-' }}</td>
                                    <td>{{ $data['il'] ?? '-' }}</td>
                                    <td>
                                        @if($submission->answered)
                                            <span class="badge bg-success">Cevaplandı</span>
                                        @else
                                            <span class="badge bg-warning">Bekliyor</span>
                                        @endif
                                    </td>
                                    <td>{{ $submission->created_at->format('d.m.Y H:i') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $submission->id }}">
                                            Detay
                                        </button>
                                    </td>
                                </tr>

                                {{-- Modal içeriği --}}
                                <div class="modal fade" id="detailModal{{ $submission->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Kayıt Detayı - ID: {{ $submission->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6>Müşteri Bilgileri</h6>
                                                        <p><strong>Ad:</strong> {{ $data['ad'] ?? '' }}</p>
                                                        <p><strong>Soyad:</strong> {{ $data['soyad'] ?? '' }}</p>
                                                        <p><strong>Email:</strong> {{ $data['email'] ?? '' }}</p>
                                                        <p><strong>Telefon:</strong> {{ $data['telefon'] ?? '' }}</p>
                                                        <p><strong>İl/İlçe:</strong> {{ $data['il'] ?? '' }}/{{ $data['ilce'] ?? '' }}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Adres Bilgileri</h6>
                                                        <p><strong>Adres:</strong> {{ $data['adres'] ?? '' }}</p>
                                                        
                                                        <h6 class="mt-3">Teknik Bilgiler</h6>
                                                        <p><strong>Gaz Kontrol:</strong> 
                                                            <span class="badge {{ ($data['gaz-kontrol'] ?? '') == 'Evet' ? 'bg-success' : 'bg-warning' }}">
                                                                {{ $data['gaz-kontrol'] ?? 'Belirtilmemiş' }}
                                                            </span>
                                                        </p>
                                                        <p><strong>Arızalı Ürün:</strong> {{ $data['arizali-urun'] ?? '' }}</p>
                                                        <p><strong>Garanti:</strong> 
                                                            <span class="badge {{ ($data['garanti'] ?? '') == 'Evet' ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $data['garanti'] ?? 'Belirtilmemiş' }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                                
                                                <div class="row mt-3">
                                                    <div class="col-12">
                                                        <h6>Kabul Şartları</h6>
                                                        <div class="border p-3">
                                                            @if(isset($data['kabul']) && is_array($data['kabul']))
                                                                <ul class="mb-0">
                                                                    @foreach($data['kabul'] as $kabulItem)
                                                                        <li>{{ $kabulItem }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @elseif(isset($data['kabul']))
                                                                <p class="mb-0">{{ $data['kabul'] }}</p>
                                                            @else
                                                                <p class="mb-0 text-muted">Kabul bilgisi bulunmuyor</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                @if(!empty($data['mesaj']))
                                                <div class="row mt-3">
                                                    <div class="col-12">
                                                        <h6>Mesaj/Not</h6>
                                                        <div class="border p-3 bg-light">
                                                            {{ $data['mesaj'] ?? '' }}
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <h6>Sistem Bilgileri</h6>
                                                        <p><strong>DB Ad Soyad:</strong> {{ $submission->name }}</p>
                                                        <p><strong>DB Email:</strong> {{ $submission->email }}</p>
                                                        <p><strong>Durum:</strong> 
                                                            @if($submission->answered)
                                                                <span class="badge bg-success">Cevaplandı</span>
                                                            @else
                                                                <span class="badge bg-warning">Bekliyor</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Oluşturulma:</strong> {{ $submission->created_at->format('d.m.Y H:i:s') }}</p>
                                                        <p><strong>Son Güncelleme:</strong> {{ $submission->updated_at->format('d.m.Y H:i:s') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Kayıt bulunamadı.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $submissions->links() }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

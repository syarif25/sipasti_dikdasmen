@extends('layouts.app')

@section('content')
<div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
  <div class="card-body px-4 py-3">
    <div class="row align-items-center">
      <div class="col-9">
        <h4 class="fw-semibold mb-8">Arsip Dokumen</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="/">Dashboard</a></li>
            <li class="breadcrumb-item" aria-current="page">Arsip</li>
          </ol>
        </nav>
      </div>
      <div class="col-3">
        <div class="text-center mb-n5">
          <i class="ti ti-archive fs-8 text-info"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h5 class="card-title fw-semibold mb-4">Daftar Dokumen Selesai / Arsip</h5>

    <!-- Filter Bar -->
    <div class="row mb-4">
      <div class="col-md-3 mb-2">
        <input type="text" id="filterPerihal" class="form-control" placeholder="Cari Perihal...">
      </div>
      <div class="col-md-3 mb-2">
        <input type="text" id="filterPengirim" class="form-control" placeholder="Cari Pengirim...">
      </div>
      <div class="col-md-3 mb-2">
        <input type="text" id="filterPosisi" class="form-control" placeholder="Cari Posisi saat ini...">
      </div>
      <div class="col-md-3 mb-2">
        <select id="filterJenisSurat" class="form-select">
          <option value="">Semua Jenis Surat</option>
          @foreach($jenisSurats as $js)
            <option value="{{ $js->nama_jenis }}">{{ $js->nama_jenis }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="table-responsive">
      <table id="dataTable" class="table align-middle border table-striped table-bordered">
        <thead class="text-dark fs-3">
          <tr>
            <th><h6 class="fw-semibold mb-0 text-uppercase fs-2">No.</h6></th>
            <th><h6 class="fw-semibold mb-0 text-uppercase fs-2">Pengirim</h6></th>
            <th><h6 class="fw-semibold mb-0 text-uppercase fs-2">Perihal</h6></th>
            <th><h6 class="fw-semibold mb-0 text-uppercase fs-2">Tujuan</h6></th>
            <th><h6 class="fw-semibold mb-0 text-uppercase fs-2">Jenis Surat</h6></th>
            <th><h6 class="fw-semibold mb-0 text-uppercase fs-2">Posisi</h6></th>
            <th><h6 class="fw-semibold mb-0 text-uppercase fs-2">Status</h6></th>
            <th><h6 class="fw-semibold mb-0 text-uppercase fs-2">Tgl</h6></th>
            <th><h6 class="fw-semibold mb-0 text-uppercase fs-2">Aksi</h6></th>
          </tr>
        </thead>
        <tbody>
          @foreach($pengajuans as $item)
          @php
            $latestLog = $item->logs->sortByDesc('id_log')->first();
            $posisi = $latestLog ? $latestLog->posisi : '-';
            $jabatanPosisi = $latestLog ? $latestLog->jabatan : '-';
            $status = $latestLog ? $latestLog->status : 'DALAM PROSES';
          @endphp
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td class="fw-bold">{{ $item->lembaga ? $item->lembaga->singkatan_lembaga : '-' }}</td>
            <td style="white-space: normal; min-width: 250px;">{{ $item->perihal }}</td>
            <td>{{ $item->tujuan }}</td>
            <td>{{ $item->jenis_surat }}</td>
            <td>
              <div class="fw-bold">{{ $posisi }}</div>
              <div class="fw-semibold text-muted">({{ $jabatanPosisi }})</div>
            </td>
            <td>
              @if($status == 'SELESAI' || $status == 'FINAL' || $status == 'DIARSIP' || $status == 'ACC KABID')
                <span class="badge bg-success text-white"><i class="ti ti-check"></i> {{ $status }}</span>
              @elseif($status == 'REVISI')
                <span class="badge bg-danger text-white"><i class="ti ti-alert-triangle"></i> REVISI</span>
              @else
                <span class="badge bg-warning text-dark"><i class="ti ti-loader"></i> {{ $status }}</span>
              @endif
            </td>
            <td>{{ \Carbon\Carbon::parse($item->tgl_upload)->format('Y-m-d H:i:s') }}</td>
            <td>
              <div class="d-flex gap-1">
                <button type="button" class="btn btn-sm btn-info btn-lacak text-white" 
                        data-id="{{ $item->id_pengajuan }}"
                        data-pengirim="{{ $item->lembaga ? $item->lembaga->singkatan_lembaga : '-' }}"
                        data-nomor="{{ $item->nomor_surat ?? '-' }}"
                        data-tujuan="{{ $item->tujuan }}"
                        data-perihal="{{ $item->perihal }}">
                  <i class="ti ti-search"></i> LACAK
                </button>
                @if($latestLog && $latestLog->file1)
                  <a href="{{ Storage::url($latestLog->file1) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-file-description"></i> FILE 1
                  </a>
                @else
                  <button class="btn btn-sm btn-outline-secondary" disabled><i class="ti ti-file-description"></i> FILE 1</button>
                @endif
                
                @if($latestLog && $latestLog->file2)
                  <a href="{{ Storage::url($latestLog->file2) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-file-description"></i> FILE 2
                  </a>
                @else
                  <button class="btn btn-sm btn-outline-secondary" disabled><i class="ti ti-file-description"></i> FILE 2</button>
                @endif
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Timeline -->
<div class="modal fade" id="modalTimeline" tabindex="-1" aria-labelledby="modalTimelineLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white" id="modalTimelineLabel"><i class="ti ti-history me-2"></i>Riwayat Pengajuan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <div class="border p-3 rounded mb-4">
          <div class="row">
            <div class="col-md-6 mb-3">
              <span class="d-block text-muted fs-2">Pengirim</span>
              <span class="d-block fw-bold text-dark" id="lacakPengirim">-</span>
            </div>
            <div class="col-md-6 mb-3">
              <span class="d-block text-muted fs-2">Nomor Surat</span>
              <span class="d-block fw-bold text-dark" id="lacakNomor">-</span>
            </div>
            <div class="col-md-6 mb-3 mb-md-0">
              <span class="d-block text-muted fs-2">Tujuan</span>
              <span class="d-block fw-bold text-dark" id="lacakTujuan">-</span>
            </div>
            <div class="col-md-6">
              <span class="d-block text-muted fs-2">Perihal</span>
              <span class="d-block fw-bold text-dark" id="lacakPerihal">-</span>
            </div>
          </div>
        </div>

        <div class="table-responsive border rounded">
          <table class="table mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th>TANGGAL</th>
                <th>POSISI</th>
                <th>CATATAN</th>
              </tr>
            </thead>
            <tbody id="timelineContent">
              <!-- Timeline items will be injected here via AJAX -->
              <tr>
                <td colspan="3" class="text-center py-4" id="timelineLoading">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if ($.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable().destroy();
    }
    var table = $('#dataTable').DataTable({
      "language": {
        "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
      },
      "ordering": false
    });

    // Custom Filters
    $('#filterPerihal').on('keyup', function() {
        table.column(2).search(this.value).draw();
    });
    $('#filterPengirim').on('keyup', function() {
        table.column(1).search(this.value).draw();
    });
    $('#filterPosisi').on('keyup', function() {
        table.column(5).search(this.value).draw();
    });
    $('#filterJenisSurat').on('change', function() {
        table.column(4).search(this.value).draw();
    });

    // Lacak Button AJAX
    $('.btn-lacak').on('click', function() {
        let idPengajuan = $(this).data('id');
        $('#lacakPengirim').text($(this).data('pengirim'));
        $('#lacakNomor').text($(this).data('nomor'));
        $('#lacakTujuan').text($(this).data('tujuan'));
        $('#lacakPerihal').text($(this).data('perihal'));

        $('#timelineContent').html('<tr><td colspan="3" class="text-center py-4" id="timelineLoading"><div class="spinner-border text-primary" role="status"></div></td></tr>');
        $('#modalTimeline').modal('show');

        fetch(`/pengajuan/${idPengajuan}/timeline`)
            .then(response => response.json())
            .then(data => {
                let html = '';
                if(data.length === 0) {
                    html = '<tr><td colspan="3" class="text-center">Tidak ada riwayat.</td></tr>';
                } else {
                    data.forEach(log => {
                        let dateObj = new Date(log.tanggal_posisi);
                        
                        let day = String(dateObj.getDate()).padStart(2, '0');
                        let months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        let month = months[dateObj.getMonth()];
                        let year = dateObj.getFullYear();
                        
                        let h = String(dateObj.getHours()).padStart(2, '0');
                        let m = String(dateObj.getMinutes()).padStart(2, '0');
                        let s = String(dateObj.getSeconds()).padStart(2, '0');
                        
                        let dateStr = `${day} ${month} ${year}, ${h}:${m}:${s}`;
                        
                        let catatan = log.catatan ? log.catatan : '-';

                        html += `
                        <tr>
                          <td style="white-space: nowrap;">${dateStr}</td>
                          <td>Surat berada di <strong>${log.posisi} (${log.jabatan})</strong></td>
                          <td>${catatan}</td>
                        </tr>
                        `;
                    });
                }
                $('#timelineContent').html(html);
            })
            .catch(error => {
                $('#timelineContent').html('<tr><td colspan="3" class="text-danger text-center">Gagal memuat riwayat.</td></tr>');
            });
    });
  });
</script>
@endpush

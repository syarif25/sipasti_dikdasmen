@extends('layouts.app')

@section('content')
<div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
  <div class="card-body px-4 py-3">
    <div class="row align-items-center">
      <div class="col-9">
        <h4 class="fw-semibold mb-8">Data Pengajuan</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="/">Dashboard</a></li>
            <li class="breadcrumb-item" aria-current="page">Pengajuan</li>
          </ol>
        </nav>
      </div>
      <div class="col-3">
        <div class="text-center mb-n5">
          <i class="ti ti-file-description fs-8 text-info"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h5 class="card-title fw-semibold">Daftar Pengajuan</h5>
      <a href="{{ route('pengajuan.create') }}" class="btn btn-primary">
        <i class="ti ti-plus"></i> ADD PENGAJUAN
      </a>
    </div>

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
            <th><h6 class="fw-semibold mb-0 text-uppercase fs-2">Tgl Kirim</h6></th>
            <th><h6 class="fw-semibold mb-0 text-uppercase fs-2">Aksi</h6></th>
            <th><h6 class="fw-semibold mb-0 text-uppercase fs-2">Detail</h6></th>
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
            <td class="fw-bold">{{ $item->lembaga ? $item->lembaga->nama_lembaga : '-' }}</td>
            <td style="white-space: normal; min-width: 200px;">{{ $item->perihal }}</td>
            <td>{{ $item->tujuan }}</td>
            <td>{{ $item->jenis_surat }}</td>
            <td>
              <div class="fw-bold">{{ $posisi }}</div>
              <div class="fw-semibold text-muted">({{ $jabatanPosisi }})</div>
            </td>
            <td>{{ \Carbon\Carbon::parse($item->tgl_upload)->format('d-m-Y H:i:s') }}</td>
            <td>
              @if(Auth::user()->level >= 2)
                @php
                  // Admin Dikdasmen/Super Admin selalu punya akses (fallback) atau jika nama_pengguna sesuai dengan tujuan
                  $isTargetedUser = (Auth::user()->level >= 7 || strtolower($jabatanPosisi) == strtolower(Auth::user()->name) || strtolower($jabatanPosisi) == 'administrator');
                @endphp
                @if($isTargetedUser)
                  @if($status == 'k' || $status == 'DALAM PROSES')
                    <form action="{{ route('pengajuan.terima', $item->id_pengajuan) }}" method="POST">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-success w-100 mb-1"><i class="ti ti-check"></i> TERIMA SURAT</button>
                    </form>
                  @elseif($status == 't' || $status == 'ACC KABID' || $status == 'REVISI')
                    <div class="d-flex flex-column gap-1">
                      <button type="button" class="btn btn-sm btn-warning w-100 mb-1" data-bs-toggle="modal" data-bs-target="#modalTeruskan{{ $item->id_pengajuan }}">
                        <i class="ti ti-arrow-right"></i> LANJUTKAN
                      </button>
                      <button type="button" class="btn btn-sm btn-danger w-100" data-bs-toggle="modal" data-bs-target="#modalKembalikan{{ $item->id_pengajuan }}">
                        <i class="ti ti-arrow-back-up"></i> KEMBALIKAN
                      </button>
                    </div>
                  @else
                    <span class="badge bg-secondary">{{ $status }}</span>
                  @endif
                @else
                  <span class="badge bg-secondary">{{ in_array($status, ['k', 'DALAM PROSES', 't']) ? 'sedang proses' : strtolower($status) }}</span>
                @endif
              @else
                <!-- Sekolah/Lembaga melihat status -->
                @if($status == 'k' || $status == 't' || $status == 'DALAM PROSES' || $status == 'ACC KABID')
                  <span class="badge bg-warning">sedang proses</span>
                @elseif($status == 'REVISI')
                  <span class="badge bg-danger">revisi</span>
                @else
                  <span class="badge bg-secondary">{{ strtolower($status) }}</span>
                @endif
              @endif
            </td>
            <td>
              <div class="d-flex flex-column gap-1">
                <button type="button" class="btn btn-sm btn-info w-100 mb-1 btn-lacak" data-id="{{ $item->id_pengajuan }}">
                  <i class="ti ti-search"></i> LACAK
                </button>
                <div class="d-flex gap-1">
                  @if($latestLog && $latestLog->file1)
                    <a href="{{ Storage::url($latestLog->file1) }}" target="_blank" class="btn btn-sm btn-outline-primary flex-fill">
                      <i class="ti ti-file-description"></i> FILE 1
                    </a>
                  @else
                    <button class="btn btn-sm btn-outline-secondary flex-fill" disabled><i class="ti ti-file-description"></i> FILE 1</button>
                  @endif
                  
                  @if($latestLog && $latestLog->file2)
                    <a href="{{ Storage::url($latestLog->file2) }}" target="_blank" class="btn btn-sm btn-outline-primary flex-fill">
                      <i class="ti ti-file-description"></i> FILE 2
                    </a>
                  @else
                    <button class="btn btn-sm btn-outline-secondary flex-fill" disabled><i class="ti ti-file-description"></i> FILE 2</button>
                  @endif
                </div>
              </div>
            </td>
          </tr>

          <!-- Modal Teruskan -->
          <div class="modal fade" id="modalTeruskan{{ $item->id_pengajuan }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                 <form action="{{ route('pengajuan.teruskan', $item->id_pengajuan) }}" method="POST" enctype="multipart/form-data">
                  @csrf
                  <div class="modal-header">
                    <h5 class="modal-title">Lanjutkan Pengajuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    @php
                      $latestLog = $item->logs->sortByDesc('id_log')->first();
                      $userLevel = auth()->user()->level;
                      $isAccKabid = ($latestLog && $latestLog->status == 'ACC KABID');
                    @endphp

                    <div class="bg-light p-3 rounded mb-4 border">
                      <dl class="row mb-0 text-dark">
                        <dt class="col-sm-4 fw-normal">Nomor Surat</dt>
                        <dd class="col-sm-8 fw-bold mb-2">{{ $item->nomor_surat }}</dd>
                        
                        <dt class="col-sm-4 fw-normal">Perihal</dt>
                        <dd class="col-sm-8 fw-bold mb-2">{{ $item->perihal }}</dd>
                        
                        <dt class="col-sm-4 fw-normal">Tujuan Surat</dt>
                        <dd class="col-sm-8 fw-bold mb-0">{{ $item->tujuan }}</dd>
                      </dl>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Tujuan (Jabatan Berikutnya)</label>
                      <select class="form-select" name="tujuan_user_id" required>
                        @if($userLevel >= 7) {{-- Admin Dikdasmen --}}
                          @if($isAccKabid)
                            <option value="BPK2M">BPK2M</option>
                            <option value="Bendahara">Bendahara</option>
                            <option value="Sekretariat">Sekretariat</option>
                          @else
                            @php
                              // Admin Dikdasmen bisa meneruskan ke Kasubag(3), Kabag(4), KATU(5)
                              $targetUsers = $usersEselon->filter(fn($u) => in_array($u->level, [3, 4, 5]));
                            @endphp
                            @foreach($targetUsers as $userTarget)
                              <option value="{{ $userTarget->id_user }}">{{ $userTarget->name }}</option>
                            @endforeach
                          @endif
                        @elseif($userLevel == 6) {{-- Kabid --}}
                          <option value="Admin Dikdasmen">Admin Dikdasmen (Telah ACC Kabid)</option>
                        @elseif($userLevel == 5) {{-- KATU --}}
                          @php $targetUsers = $usersEselon->filter(fn($u) => $u->level == 6); @endphp
                          @foreach($targetUsers as $userTarget)
                            <option value="{{ $userTarget->id_user }}">{{ $userTarget->name }}</option>
                          @endforeach
                        @elseif($userLevel == 4) {{-- Kabag --}}
                          @php $targetUsers = $usersEselon->filter(fn($u) => $u->level == 5); @endphp
                          @foreach($targetUsers as $userTarget)
                            <option value="{{ $userTarget->id_user }}">{{ $userTarget->name }}</option>
                          @endforeach
                        @elseif($userLevel == 3) {{-- Kasubag --}}
                          @php $targetUsers = $usersEselon->filter(fn($u) => $u->level == 4); @endphp
                          @foreach($targetUsers as $userTarget)
                            <option value="{{ $userTarget->id_user }}">{{ $userTarget->name }}</option>
                          @endforeach
                        @else {{-- Fallback --}}
                          <option value="Admin Dikdasmen">Admin Dikdasmen</option>
                        @endif
                      </select>
                    </div>

                    @if($userLevel >= 7 && $isAccKabid)
                      <div class="mb-3">
                        <label class="form-label text-danger fw-bold">File 1 (Pengantar Baru) *</label>
                        <input class="form-control" type="file" name="file1" accept=".pdf" required>
                        <small class="text-muted">Wajib diunggah untuk menyatukan berkas sekolah + pengantar Dikdasmen.</small>
                      </div>
                      <div class="mb-3">
                        <label class="form-label fw-bold">File 2 (Lampiran Baru - Opsional)</label>
                        <input class="form-control" type="file" name="file2" accept=".pdf">
                        <small class="text-muted">Opsional. Biarkan kosong jika tidak ingin diubah.</small>
                      </div>
                    @endif

                    <div class="mb-3">
                      <label class="form-label">Catatan</label>
                      <textarea class="form-control" name="catatan" rows="3"></textarea>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Lanjutkan</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Modal Kembalikan -->
          <div class="modal fade" id="modalKembalikan{{ $item->id_pengajuan }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form action="{{ route('pengajuan.kembalikan', $item->id_pengajuan) }}" method="POST">
                  @csrf
                  <div class="modal-header">
                    <h5 class="modal-title">Kembalikan / Revisi Pengajuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="bg-light p-3 rounded mb-4 border">
                      <dl class="row mb-0 text-dark">
                        <dt class="col-sm-4 fw-normal">Nomor Surat</dt>
                        <dd class="col-sm-8 fw-bold mb-2">{{ $item->nomor_surat }}</dd>
                        
                        <dt class="col-sm-4 fw-normal">Perihal</dt>
                        <dd class="col-sm-8 fw-bold mb-2">{{ $item->perihal }}</dd>
                        
                        <dt class="col-sm-4 fw-normal">Tujuan Surat</dt>
                        <dd class="col-sm-8 fw-bold mb-2">{{ $item->tujuan }}</dd>
                        
                        <dt class="col-sm-4 fw-normal">Pengirim</dt>
                        <dd class="col-sm-8 fw-bold mb-0">{{ $item->lembaga ? $item->lembaga->nama_lembaga : '-' }}</dd>
                      </dl>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Isi Catatan Revisi</label>
                      <textarea class="form-control" name="catatan" rows="4" required placeholder="Tulis alasan pengembalian surat..."></textarea>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Kembalikan</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Timeline -->
<div class="modal fade" id="modalTimeline" tabindex="-1" aria-labelledby="modalTimelineLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTimelineLabel">Riwayat Perjalanan Dokumen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="timeline-widget mb-0 position-relative mb-n5" id="timelineContent">
          <!-- Timeline items will be injected here via AJAX -->
          <div class="text-center py-4" id="timelineLoading">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </ul>
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
      "ordering": false // custom column sorting if needed
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
        $('#timelineContent').html('<div class="text-center py-4" id="timelineLoading"><div class="spinner-border text-primary" role="status"></div></div>');
        $('#modalTimeline').modal('show');

        fetch(`/pengajuan/${idPengajuan}/timeline`)
            .then(response => response.json())
            .then(data => {
                let html = '';
                if(data.length === 0) {
                    html = '<p class="text-center">Tidak ada riwayat.</p>';
                } else {
                    data.forEach(log => {
                        let color = 'primary';
                        if(log.status == 'SELESAI' || log.status == 'FINAL' || log.posisi == 'Diterima') color = 'success';
                        if(log.status == 'REVISI') color = 'danger';

                        let dateObj = new Date(log.tanggal_posisi);
                        let dateStr = dateObj.toLocaleString('id-ID');

                        html += `
                        <li class="timeline-item d-flex position-relative overflow-hidden">
                          <div class="timeline-time text-dark flex-shrink-0 text-end">${dateStr}</div>
                          <div class="timeline-badge-wrap d-flex flex-column align-items-center">
                            <span class="timeline-badge border-2 border border-${color} flex-shrink-0 my-8"></span>
                            <span class="timeline-badge-border d-block flex-shrink-0"></span>
                          </div>
                          <div class="timeline-desc fs-3 text-dark mt-n1 fw-semibold">
                            ${log.posisi} (${log.jabatan})
                            <div class="fw-normal text-muted mt-1">${log.catatan ? log.catatan : ''}</div>
                            <span class="badge bg-${color} mt-2">${log.status}</span>
                          </div>
                        </li>
                        `;
                    });
                }
                $('#timelineContent').html(html);
            })
            .catch(error => {
                $('#timelineContent').html('<p class="text-danger text-center">Gagal memuat riwayat.</p>');
            });
    });
  });
</script>
@endpush

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

      <table id="dataTable" class="table align-middle border table-striped table-bordered dt-responsive" style="width:100%">
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
            $file1Log = $item->logs->whereNotNull('file1')->sortByDesc('id_log')->first();
            $file2Log = $item->logs->whereNotNull('file2')->sortByDesc('id_log')->first();
            $currentFile1 = $file1Log ? $file1Log->file1 : null;
            $currentFile2 = $file2Log ? $file2Log->file2 : null;
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
            <td>{{ $latestLog ? \Carbon\Carbon::parse($latestLog->tanggal_posisi)->format('d-m-Y H:i:s') : '-' }}</td>
            <td>
              @if(Auth::user()->level >= 2)
                @php
                  // Admin Dikdasmen is targeted if document is new (administrator), ACC Kabid, or specifically forwarded to their name.
                  // Other users are targeted only if the document is specifically at their name.
                  $isTargetedUser = false;
                  if (Auth::user()->level >= 7) {
                      if (strtolower($jabatanPosisi) == 'administrator' || $status == 'ACC KABID' || strtolower($jabatanPosisi) == strtolower(Auth::user()->name)) {
                          $isTargetedUser = true;
                      }
                  } else {
                      if (strtolower($jabatanPosisi) == strtolower(Auth::user()->name)) {
                          $isTargetedUser = true;
                      }
                  }
                @endphp
                @if($isTargetedUser)
                  @if(Auth::user()->level >= 7 && $status == 'k')
                    <form action="{{ route('pengajuan.terima', $item->id_pengajuan) }}" method="POST">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-success w-100 mb-1"><i class="ti ti-check"></i> TERIMA SURAT</button>
                    </form>
                  @elseif($status == 't' || $status == 'DALAM PROSES' || $status == 'ACC KABID' || $status == 'REVISI' || $status == 'KEMBALIKAN KE STAF')
                    <div class="d-flex flex-column gap-1">
                      @php
                        $isKabidFinal = false;
                        if(Auth::user()->level == 6) {
                            $logsDesc = $item->logs->sortByDesc('id_log');
                            foreach($logsDesc as $l) {
                                $logUser = $usersEselon->first(fn($u) => strtolower($u->name) == strtolower($l->jabatan));
                                if ($logUser) {
                                    if ($logUser->level >= 7) break;
                                    if (in_array($logUser->level, [3, 4, 5])) {
                                        $isKabidFinal = true;
                                        break;
                                    }
                                }
                            }
                        }
                      @endphp
                      <button type="button" class="btn btn-sm {{ $isKabidFinal ? 'btn-success' : 'btn-primary' }} w-100 mb-1" data-bs-toggle="modal" data-bs-target="#modalTeruskan{{ $item->id_pengajuan }}">
                        @if($isKabidFinal)
                          <i class="ti ti-check"></i> ACC
                        @else
                          <i class="ti ti-arrow-right"></i> LANJUTKAN
                        @endif
                      </button>
                      @if($status != 'ACC KABID')
                        <button type="button" class="btn btn-sm btn-danger w-100" data-bs-toggle="modal" data-bs-target="#modalKembalikan{{ $item->id_pengajuan }}">
                          <i class="ti ti-arrow-back-up"></i> KEMBALIKAN
                        </button>
                      @endif
                    </div>
                  @else
                    <span class="badge bg-secondary">{{ $status }}</span>
                  @endif
                @else
                  <span class="badge bg-secondary">{{ in_array($status, ['k', 'DALAM PROSES', 't', 'KEMBALIKAN KE STAF']) ? 'sedang proses' : strtolower($status) }}</span>
                @endif
              @else
                <!-- Sekolah/Lembaga melihat status -->
                @if($status == 'k' || $status == 't' || $status == 'DALAM PROSES' || $status == 'ACC KABID')
                  <span class="badge bg-warning">sedang proses</span>
                @elseif($status == 'REVISI')
                  <button type="button" class="btn btn-sm btn-danger w-100" data-bs-toggle="modal" data-bs-target="#modalRevisi{{ $item->id_pengajuan }}">
                    <i class="ti ti-edit"></i> REVISI
                  </button>
                @else
                  <span class="badge bg-secondary">{{ strtolower($status) }}</span>
                @endif
              @endif
            </td>
            <td>
              <div class="d-flex flex-column gap-1">
                <button type="button" class="btn btn-sm btn-info w-100 mb-1 btn-lacak" 
                  data-id="{{ $item->id_pengajuan }}"
                  data-nomor="{{ $item->nomor_surat }}"
                  data-perihal="{{ $item->perihal }}"
                  data-pengirim="{{ $item->lembaga ? $item->lembaga->nama_lembaga : '-' }}"
                  data-tujuan="{{ $item->tujuan }}">
                  <i class="ti ti-search"></i> LACAK
                </button>
                <div class="d-flex gap-1">
                  @if($currentFile1)
                    <a href="{{ Storage::url($currentFile1) }}" target="_blank" class="btn btn-sm btn-outline-primary flex-fill">
                      <i class="ti ti-file-description"></i> FILE 1
                    </a>
                  @else
                    <button class="btn btn-sm btn-outline-secondary flex-fill" disabled><i class="ti ti-file-description"></i> FILE 1</button>
                  @endif
                  
                  @if($currentFile2)
                    <a href="{{ Storage::url($currentFile2) }}" target="_blank" class="btn btn-sm btn-outline-primary flex-fill">
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
                  <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">
                      <i class="ti ti-check me-2"></i>
                      @if(Auth::user()->level == 6)
                        ACC Pengajuan
                      @else
                        Lanjutkan Pengajuan
                      @endif
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    @php
                      $latestLog = $item->logs->sortByDesc('id_log')->first();
                      $userLevel = auth()->user()->level;
                      $isAccKabid = ($latestLog && $latestLog->status == 'ACC KABID');
                    @endphp

                    <div class="border p-3 rounded mb-4">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <span class="d-block text-muted fs-2">Pengirim</span>
                          <span class="d-block fw-bold text-dark">{{ $item->lembaga ? $item->lembaga->nama_lembaga : '-' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                          <span class="d-block text-muted fs-2">Nomor Surat</span>
                          <span class="d-block fw-bold text-dark">{{ $item->nomor_surat }}</span>
                        </div>
                        <div class="col-md-6 mb-3 mb-md-0">
                          <span class="d-block text-muted fs-2">Tujuan</span>
                          <span class="d-block fw-bold text-dark">{{ $item->tujuan }}</span>
                        </div>
                        <div class="col-md-6">
                          <span class="d-block text-muted fs-2">Perihal</span>
                          <span class="d-block fw-bold text-dark">{{ $item->perihal }}</span>
                        </div>
                      </div>
                    </div>

                      <div class="mb-3">
                        <label class="form-label">Tujuan Berikutnya</label>
                        <select class="form-select" name="tujuan_user_id" required>
                          <option value="">-- Pilih Jabatan --</option>
                          @if($userLevel >= 7) {{-- Admin Dikdasmen --}}
                            @if($isAccKabid)
                              <option value="BPK2M">BPK2M</option>
                              <option value="Bendahara">Bendahara</option>
                              <option value="Sekretariat">Sekretariat</option>
                            @else
                              @php
                                $targetUsers = $usersEselon->filter(fn($u) => $u->level == 6);
                              @endphp
                              @foreach($targetUsers as $userTarget)
                                <option value="{{ $userTarget->id_user }}">{{ $userTarget->name }}</option>
                              @endforeach
                            @endif
                          @elseif($userLevel == 6) {{-- Kabid --}}
                            @php
                                $logsDesc = $item->logs->sortByDesc('id_log');
                                $hasProcessedDown = false;
                                foreach($logsDesc as $log) {
                                    $logUser = $usersEselon->first(fn($u) => strtolower($u->name) == strtolower($log->jabatan));
                                    if ($logUser) {
                                        if ($logUser->level >= 7) {
                                            break; // Found Staf/Admin Dikdasmen first, meaning it just started a new cycle
                                        }
                                        if (in_array($logUser->level, [3, 4, 5])) {
                                            $hasProcessedDown = true;
                                            break; // Found KATU/Kabag/Kasubag before Staf, meaning it has processed down
                                        }
                                    }
                                }
                                $allowedLevels = $hasProcessedDown ? [7] : [4, 5, 7];
                                $targetUsers = $usersEselon->filter(fn($u) => in_array($u->level, $allowedLevels));
                            @endphp
                            @foreach($targetUsers as $userTarget)
                                <option value="{{ $userTarget->id_user }}">{{ $userTarget->name }}</option>
                            @endforeach
                          @elseif($userLevel == 5) {{-- KATU --}}
                            @php
                                $targetUsers = $usersEselon->filter(fn($u) => in_array($u->level, [3, 4, 6]));
                            @endphp
                            @foreach($targetUsers as $userTarget)
                              <option value="{{ $userTarget->id_user }}">{{ $userTarget->name }}</option>
                            @endforeach
                          @elseif($userLevel == 4) {{-- Kabag --}}
                            @php 
                                $targetUsers = $usersEselon->filter(fn($u) => in_array($u->level, [3, 4, 5, 6])); 
                            @endphp
                            @foreach($targetUsers as $userTarget)
                              <option value="{{ $userTarget->id_user }}">{{ $userTarget->name }}</option>
                            @endforeach
                          @elseif($userLevel == 3) {{-- Kasubag --}}
                            @php $targetUsers = $usersEselon->filter(fn($u) => $u->level == 5); @endphp
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
                      </div>
                      <div class="mb-3">
                        <label class="form-label fw-bold">File 2 (Lampiran)</label>
                        <div class="d-flex gap-4 mb-2">
                          <div class="form-check">
                            <input class="form-check-input" type="radio" name="file2_option_{{ $item->id_pengajuan }}" id="file2_no_change_{{ $item->id_pengajuan }}" value="no_change" checked onchange="document.getElementById('file2_input_container_{{ $item->id_pengajuan }}').style.display='none'">
                            <label class="form-check-label" for="file2_no_change_{{ $item->id_pengajuan }}">
                              Tidak ada perubahan
                            </label>
                          </div>
                          <div class="form-check">
                            <input class="form-check-input" type="radio" name="file2_option_{{ $item->id_pengajuan }}" id="file2_change_{{ $item->id_pengajuan }}" value="change" onchange="document.getElementById('file2_input_container_{{ $item->id_pengajuan }}').style.display='block'">
                            <label class="form-check-label" for="file2_change_{{ $item->id_pengajuan }}">
                              Ada perubahan
                            </label>
                          </div>
                        </div>
                        <div id="file2_input_container_{{ $item->id_pengajuan }}" style="display: none;">
                          <input class="form-control" type="file" name="file2" accept=".pdf">
                          <small class="text-muted">Unggah file lampiran baru.</small>
                        </div>
                      </div>
                    @endif

                      <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" name="catatan" rows="3" placeholder="Tulis instruksi/catatan (Opsional)..."></textarea>
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
                  <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white"><i class="ti ti-arrow-back-up me-2"></i>Kembalikan / Revisi Pengajuan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="border p-3 rounded mb-4">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <span class="d-block text-muted fs-2">Pengirim</span>
                          <span class="d-block fw-bold text-dark">{{ $item->lembaga ? $item->lembaga->nama_lembaga : '-' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                          <span class="d-block text-muted fs-2">Nomor Surat</span>
                          <span class="d-block fw-bold text-dark">{{ $item->nomor_surat }}</span>
                        </div>
                        <div class="col-md-6 mb-3 mb-md-0">
                          <span class="d-block text-muted fs-2">Tujuan</span>
                          <span class="d-block fw-bold text-dark">{{ $item->tujuan }}</span>
                        </div>
                        <div class="col-md-6">
                          <span class="d-block text-muted fs-2">Perihal</span>
                          <span class="d-block fw-bold text-dark">{{ $item->perihal }}</span>
                        </div>
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Isi Catatan Revisi</label>
                      <textarea class="form-control" name="catatan" rows="4" placeholder="Tulis alasan pengembalian surat (Opsional)..."></textarea>
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

          <!-- Modal Revisi -->
          <div class="modal fade" id="modalRevisi{{ $item->id_pengajuan }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form action="{{ route('pengajuan.update', $item->id_pengajuan) }}" method="POST" enctype="multipart/form-data">
                  @csrf
                  @method('PUT')
                  <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white"><i class="ti ti-upload me-2"></i>Upload Ulang Revisi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="border p-3 rounded mb-4">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <span class="d-block text-muted fs-2">Pengirim</span>
                          <span class="d-block fw-bold text-dark">{{ $item->lembaga ? $item->lembaga->nama_lembaga : '-' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                          <span class="d-block text-muted fs-2">Nomor Surat</span>
                          <span class="d-block fw-bold text-dark">{{ $item->nomor_surat }}</span>
                        </div>
                        <div class="col-md-6 mb-3 mb-md-0">
                          <span class="d-block text-muted fs-2">Tujuan</span>
                          <span class="d-block fw-bold text-dark">{{ $item->tujuan }}</span>
                        </div>
                        <div class="col-md-6">
                          <span class="d-block text-muted fs-2">Perihal</span>
                          <span class="d-block fw-bold text-dark">{{ $item->perihal }}</span>
                        </div>
                      </div>
                    </div>

                    <div class="alert alert-danger mb-4">
                      <strong>Catatan Revisi:</strong><br>
                      {{ $latestLog && $latestLog->catatan ? $latestLog->catatan : 'Tidak ada catatan.' }}
                    </div>

                    <div class="mb-3">
                      <label class="form-label fw-bold">File 1 (Surat Pengantar Baru) *</label>
                      <input class="form-control" type="file" name="file1" accept=".pdf" required>
                      <small class="text-muted">Wajib diunggah (Format PDF max 10MB).</small>
                    </div>

                    <div class="mb-3">
                      <label class="form-label fw-bold">File 2 (Lampiran Baru)</label>
                      <div class="d-flex gap-4 mb-2">
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="file2_option_{{ $item->id_pengajuan }}" id="revisi_file2_no_change_{{ $item->id_pengajuan }}" value="no_change" checked onchange="document.getElementById('revisi_file2_input_container_{{ $item->id_pengajuan }}').style.display='none'">
                          <label class="form-check-label" for="revisi_file2_no_change_{{ $item->id_pengajuan }}">
                            Tetap gunakan file lama
                          </label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="file2_option_{{ $item->id_pengajuan }}" id="revisi_file2_change_{{ $item->id_pengajuan }}" value="change" onchange="document.getElementById('revisi_file2_input_container_{{ $item->id_pengajuan }}').style.display='block'">
                          <label class="form-check-label" for="revisi_file2_change_{{ $item->id_pengajuan }}">
                            Ada perubahan file
                          </label>
                        </div>
                      </div>
                      <div id="revisi_file2_input_container_{{ $item->id_pengajuan }}" style="display: none;">
                        <input class="form-control" type="file" name="file2" accept=".pdf">
                        <small class="text-muted">Unggah file lampiran revisi Format PDF max 10MB.</small>
                      </div>
                    </div>

                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-send"></i> Kirim Ulang</button>
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
      "ordering": true, // Enable column sorting
      "responsive": true
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
                        let color = 'primary';
                        if(log.status == 'SELESAI' || log.status == 'FINAL' || log.posisi == 'Diterima') color = 'success';
                        if(log.status == 'REVISI') color = 'danger';

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
                        
                        let descHtml = `surat dilanjutkan ke <strong>${log.posisi} (${log.jabatan})</strong>`;
                        if (log.status == 'k') {
                            descHtml = `surat <strong>dikirim</strong> ke <strong>${log.posisi} (${log.jabatan})</strong>`;
                        } else if (log.status == 't') {
                            descHtml = `surat <strong>diterima</strong> oleh <strong>${log.posisi} (${log.jabatan})</strong>`;
                        } else if (log.status == 'ACC KABID') {
                            descHtml = `<strong>surat telah ditandatangani kabid dikdasmen</strong>`;
                        } else if (log.status == 'REVISI' || log.status == 'KEMBALIKAN KE STAF') {
                            descHtml = `surat <strong>dikembalikan ke pengirim</strong>`;
                        }

                        html += `
                        <tr>
                          <td style="white-space: nowrap;">${dateStr}</td>
                          <td>${descHtml}</td>
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

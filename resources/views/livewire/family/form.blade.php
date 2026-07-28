<?php
use App\Models\Family;
use App\Models\FamilyMember;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.dashboard')] class extends Component {
    public string $type = 'head';

    // Form Head
    public $head_nik = '';
    public $no_kk = '';
    public $head_name = '';
    public $province_code = '';
    public $city_code = '';
    public $district_code = '';
    public $village_code = '';
    public $address_rt = '';
    public $address_rw = '';
    
    // Form Member
    public $member_nik = '';
    public $member_name = '';
    public $birth_date = '';
    public $gender = 'L';
    
    // Dropdown Data
    public $provinces = [];
    public $cities = [];
    public $districts = [];
    public $villages = [];

    public function mount()
    {
        $this->type = request()->query('type', 'head');
        $user = Auth::user();
        
        if ($this->type === 'head') {
            $family = Family::where('user_id', $user->id)->first();
            $this->provinces = Province::orderBy('name')->get();
            
            if ($family) {
                $this->head_nik = $family->head_of_family_nik;
                $this->no_kk = $family->no_kk;
                $this->head_name = $family->head_of_family_name;
                $this->province_code = $family->province_code;
                $this->address_rt = $family->address_rt;
                $this->address_rw = $family->address_rw;
                
                $this->updatedProvinceCode($this->province_code);
                $this->city_code = $family->city_code;
                $this->updatedCityCode($this->city_code);
                $this->district_code = $family->district_code;
                $this->updatedDistrictCode($this->district_code);
                $this->village_code = $family->village_code;
            } else {
                $this->head_name = $user->name;
                $this->head_nik = $user->nik ?? '';
            }
        }
    }
    
    public function updatedProvinceCode($value)
    {
        $this->cities = City::where('province_code', $value)->orderBy('name')->get();
        $this->city_code = '';
        $this->district_code = '';
        $this->village_code = '';
        $this->districts = [];
        $this->villages = [];
    }

    public function updatedCityCode($value)
    {
        $this->districts = District::where('city_code', $value)->orderBy('name')->get();
        $this->district_code = '';
        $this->village_code = '';
        $this->villages = [];
    }

    public function updatedDistrictCode($value)
    {
        $this->villages = Village::where('district_code', $value)->orderBy('name')->get();
        $this->village_code = '';
    }

    public function saveHead()
    {
        $this->validate([
            'head_nik' => 'required|string|size:16',
            'no_kk' => 'nullable|string|size:16',
            'head_name' => 'required|string|max:255',
            'province_code' => 'required|string',
            'city_code' => 'required|string',
            'district_code' => 'required|string',
            'village_code' => 'required|string',
            'address_rt' => 'required|string|max:3',
            'address_rw' => 'required|string|max:3',
        ]);
        
        $user = Auth::user();
        $family = Family::updateOrCreate(
            ['user_id' => $user->id],
            [
                'head_of_family_nik' => $this->head_nik,
                'no_kk' => $this->no_kk,
                'head_of_family_name' => $this->head_name,
                'province_code' => $this->province_code,
                'city_code' => $this->city_code,
                'district_code' => $this->district_code,
                'village_code' => $this->village_code,
                'address_rt' => $this->address_rt,
                'address_rw' => $this->address_rw,
            ]
        );
        
        return redirect()->route('family.index');
    }
    
    public function saveMember()
    {
        $this->validate([
            'member_name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'required|in:L,P',
            'member_nik' => 'nullable|string|max:16',
        ]);
        
        $family = Family::where('user_id', Auth::id())->first();
        if (!$family) {
            return redirect()->route('family.form', ['type' => 'head']);
        }
        
        FamilyMember::create([
            'family_id' => $family->id,
            'nik' => $this->member_nik ?: null,
            'name' => $this->member_name,
            'birth_date' => $this->birth_date,
            'gender' => $this->gender,
            'relation' => 'Anak',
        ]);
        
        return redirect()->route('family.index');
    }
}; ?>

<div>
    <div class="mb-4">
        <a href="{{ route('family.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Data Keluarga
        </a>
        <h2 class="fw-bold mb-1">
            {{ $type === 'head' ? 'Form Profil Keluarga' : 'Form Tambah Anak / Balita' }}
        </h2>
    </div>

    <div class="card border-0 shadow-sm rounded-4 max-w-2xl">
        <div class="card-body p-4 p-md-5">
            @if($type === 'head')
                <form wire:submit="saveHead">
                    <h5 class="fw-bold text-primary mb-3">Informasi Kepala Keluarga</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Nomor Kartu Keluarga (KK)</label>
                            <input wire:model="no_kk" type="text" class="form-control" maxlength="16" placeholder="Opsional">
                            @error('no_kk') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nomor Induk Kependudukan (NIK)</label>
                            <input wire:model="head_nik" type="text" class="form-control" maxlength="16" required>
                            @error('head_nik') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama Kepala Keluarga</label>
                            <input wire:model="head_name" type="text" class="form-control" required>
                            @error('head_name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <h5 class="fw-bold text-primary mb-3">Alamat Domisili</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Provinsi</label>
                            <select wire:model.live="province_code" class="form-select" required>
                                <option value="">Pilih Provinsi</option>
                                @foreach($provinces as $prov)
                                    <option value="{{ $prov->code }}">{{ $prov->name }}</option>
                                @endforeach
                            </select>
                            @error('province_code') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kabupaten / Kota</label>
                            <select wire:model.live="city_code" class="form-select" required {{ empty($cities) ? 'disabled' : '' }}>
                                <option value="">Pilih Kab/Kota</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->code }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                            @error('city_code') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kecamatan</label>
                            <select wire:model.live="district_code" class="form-select" required {{ empty($districts) ? 'disabled' : '' }}>
                                <option value="">Pilih Kecamatan</option>
                                @foreach($districts as $dist)
                                    <option value="{{ $dist->code }}">{{ $dist->name }}</option>
                                @endforeach
                            </select>
                            @error('district_code') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kelurahan / Desa</label>
                            <select wire:model.live="village_code" class="form-select" required {{ empty($villages) ? 'disabled' : '' }}>
                                <option value="">Pilih Kel/Desa</option>
                                @foreach($villages as $vill)
                                    <option value="{{ $vill->code }}">{{ $vill->name }}</option>
                                @endforeach
                            </select>
                            @error('village_code') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">RT</label>
                            <input wire:model="address_rt" type="text" class="form-control" placeholder="001" maxlength="3" required>
                            @error('address_rt') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">RW</label>
                            <input wire:model="address_rw" type="text" class="form-control" placeholder="002" maxlength="3" required>
                            @error('address_rw') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold rounded-pill shadow-sm">
                            <span wire:loading.remove wire:target="saveHead">Simpan Profil Keluarga</span>
                            <span wire:loading wire:target="saveHead">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            @else
                <form wire:submit="saveMember">
                    <h5 class="fw-bold text-primary mb-3">Informasi Balita</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label">Nama Lengkap Anak</label>
                            <input wire:model="member_name" type="text" class="form-control" required>
                            @error('member_name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Lahir</label>
                            <input wire:model="birth_date" type="date" class="form-control" required>
                            @error('birth_date') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Kelamin</label>
                            <select wire:model="gender" class="form-select" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            @error('gender') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">NIK (Opsional)</label>
                            <input wire:model="member_nik" type="text" class="form-control" maxlength="16" placeholder="Masukkan NIK jika sudah memiliki KIA">
                            @error('member_nik') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold rounded-pill shadow-sm">
                            <span wire:loading.remove wire:target="saveMember">Tambah Balita</span>
                            <span wire:loading wire:target="saveMember">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

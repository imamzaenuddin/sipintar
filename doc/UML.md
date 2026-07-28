# UML Documentation - Aplikasi SiPintar (Sistem Informasi Posyandu Pintar)

Dokumen ini berisi representasi diagram UML *(Unified Modeling Language)* yang lengkap untuk memvisualisasikan struktur basis data, interaksi aktor, kelas model, serta alur sistem aplikasi SiPintar.

---

## 1. Entity Relationship Diagram (ERD)

Diagram berikut mendeskripsikan relasi antar entitas utama yang ada pada sistem basis data.

```mermaid
erDiagram
    USER {
        bigint id PK
        string name
        string email
        string password
        enum role "admin, kader, warga"
    }

    FAMILY {
        bigint id PK
        bigint user_id FK "Pemilik akun keluarga (Warga)"
        string no_kk
        string head_of_family
        string address
        string rt
        string rw
        char province_id FK
        char city_id FK
        char district_id FK
        char village_id FK
    }

    FAMILY_MEMBER {
        bigint id PK
        bigint family_id FK
        string nik
        string name
        date birth_date
        enum gender "L, P"
        string blood_type
        string phone
    }

    KMS_RECORD {
        bigint id PK
        bigint family_member_id FK
        bigint recorder_id FK "Kader pencatat"
        date recorded_date
        float weight
        float height
        string blood_pressure
        float belly_circumference
        int blood_sugar
        float uric_acid
        int cholesterol
        string status_gizi
        float z_score
        text examination_notes
    }

    SCHEDULE {
        bigint id PK
        bigint created_by FK "Kader pembuat jadwal"
        string title
        date schedule_date
        json target_groups "['balita', 'remaja', 'produktif', 'lansia']"
        string location
        text description
    }

    USER ||--o{ FAMILY : "Memiliki akun"
    USER ||--o{ KMS_RECORD : "Mencatat"
    USER ||--o{ SCHEDULE : "Membuat"
    FAMILY ||--o{ FAMILY_MEMBER : "Terdiri dari"
    FAMILY_MEMBER ||--o{ KMS_RECORD : "Riwayat Pengukuran"
```

---

## 2. Class Diagram

Memetakan kelas-kelas utama (berbasis Model di Laravel) beserta atribut dan perilakunya.

```mermaid
classDiagram
    class User {
        +BigInt id
        +String name
        +String email
        +String password
        +String role
        +login()
        +logout()
    }

    class Family {
        +BigInt id
        +BigInt user_id
        +String no_kk
        +String head_of_family
        +String address
        +getMembers()
    }

    class FamilyMember {
        +BigInt id
        +BigInt family_id
        +String nik
        +String name
        +Date birth_date
        +String gender
        +getKmsRecords()
        +getAgeInMonths()
    }

    class Schedule {
        +BigInt id
        +BigInt created_by
        +String title
        +Date schedule_date
        +String location
        +Array target_groups
        +isUpcoming()
    }

    class KmsRecord {
        +BigInt id
        +BigInt family_member_id
        +BigInt recorder_id
        +Date recorded_date
        +Float weight
        +Float height
        +String status_gizi
        +String examination_notes
        +calculateStatusGizi()
        +generateMedicalNotes()
    }

    User "1" -- "*" Family : Mengelola Akun
    User "1" -- "*" Schedule : Membuat
    User "1" -- "*" KmsRecord : Mencatat
    Family "1" *-- "*" FamilyMember : Terdiri dari
    FamilyMember "1" *-- "*" KmsRecord : Riwayat Pengukuran
```

---

## 3. Use Case Diagram

Menggunakan pendekatan Flowchart LR untuk merender interaksi Aktor dengan 7 Use Case utama secara rapi.

```mermaid
flowchart LR
    %% Aktor
    Kader((Kader / Admin))
    Warga((Warga / Masyarakat))

    %% Batasan Sistem (System Boundary)
    subgraph "Sistem SiPintar"
        UC1([1. Kelola Data Keluarga & Anggota])
        UC2([2. Kelola Agenda Pelaksanaan])
        UC3([3. Input Pengukuran KMS/ILP])
        UC4([4. Lihat Laporan & Rekapitulasi])
        
        UC5([5. Akses Data Keluarga Pribadi])
        UC6([6. Lihat Jadwal Posyandu Terdekat])
        UC7([7. Pantau Grafik Pertumbuhan KMS])
    end

    %% Akses Kader
    Kader --> UC1
    Kader --> UC2
    Kader --> UC3
    Kader --> UC4
    Kader --> UC7

    %% Akses Warga
    Warga --> UC5
    Warga --> UC6
    Warga --> UC7
```

---

## 4. Activity Diagram (7 Aksi Use Case)

### UC1. Kelola Data Keluarga & Anggota
```mermaid
stateDiagram-v2
    [*] --> BukaMenuKeluarga
    BukaMenuKeluarga --> LihatDaftar
    LihatDaftar --> KlikTambahEdit
    KlikTambahEdit --> IsiFormulir
    IsiFormulir --> ValidasiSistem
    ValidasiSistem --> IsiFormulir : Data Tidak Valid
    ValidasiSistem --> SimpanDB : Data Valid
    SimpanDB --> TampilNotifikasiSukses
    TampilNotifikasiSukses --> [*]
```

### UC2. Kelola Agenda Pelaksanaan
```mermaid
stateDiagram-v2
    [*] --> BukaMenuAgenda
    BukaMenuAgenda --> LihatKalenderJadwal
    LihatKalenderJadwal --> BuatJadwalBaru
    BuatJadwalBaru --> InputTanggalDanSasaran
    InputTanggalDanSasaran --> ValidasiJadwal
    ValidasiJadwal --> SimpanJadwal : Valid
    SimpanJadwal --> [*]
```

### UC3. Input Pengukuran KMS/ILP
```mermaid
stateDiagram-v2
    [*] --> BukaDataSasaran
    BukaDataSasaran --> PilihAnggotaKeluarga
    PilihAnggotaKeluarga --> KlikTombolKMS
    KlikTombolKMS --> IsiHasilPengukuran
    IsiHasilPengukuran --> Submit
    Submit --> ProsesAutoDiagnosis
    ProsesAutoDiagnosis --> SimpanKmsRecord
    SimpanKmsRecord --> UpdateGrafik
    UpdateGrafik --> [*]
```

### UC4. Lihat Laporan & Rekapitulasi
```mermaid
stateDiagram-v2
    [*] --> BukaMenuLaporan
    BukaMenuLaporan --> PilihPeriodeDanFilter
    PilihPeriodeDanFilter --> ProsesTarikData
    ProsesTarikData --> TampilkanTabelDanGrafik
    TampilkanTabelDanGrafik --> CetakAtauExport
    CetakAtauExport --> [*]
```

### UC5. Akses Data Keluarga Pribadi
```mermaid
stateDiagram-v2
    [*] --> LoginWarga
    LoginWarga --> TampilDashboardWarga
    TampilDashboardWarga --> KlikDataKeluarga
    KlikDataKeluarga --> LihatDaftarAnggotaKeluarga
    LihatDaftarAnggotaKeluarga --> [*]
```

### UC6. Lihat Jadwal Posyandu Terdekat
```mermaid
stateDiagram-v2
    [*] --> BukaDashboardWarga
    BukaDashboardWarga --> CekWidgetJadwal
    CekWidgetJadwal --> TampilkanJadwalTerdekat
    TampilkanJadwalTerdekat --> BacaLokasiDanWaktu
    BacaLokasiDanWaktu --> [*]
```

### UC7. Pantau Grafik Pertumbuhan KMS
```mermaid
stateDiagram-v2
    [*] --> MasukMenuKMS
    MasukMenuKMS --> RequestDataGrafik
    RequestDataGrafik --> RenderCanvasChartJS
    RenderCanvasChartJS --> TampilKurvaPertumbuhan
    TampilKurvaPertumbuhan --> ScrollRiwayatPengukuran
    ScrollRiwayatPengukuran --> [*]
```

---

## 5. Sequence Diagram (Detail CRUD & Interaksi)

### UC1. Kelola Data Keluarga & Anggota (CRUD)
```mermaid
sequenceDiagram
    actor Kader
    participant View as Halaman Keluarga
    participant Controller as FamilyController
    participant tbl_families as Table: families
    participant tbl_family_members as Table: family_members

    alt Create (Tambah Data)
        Kader->>View: Mengisi form KK & Anggota
        View->>Controller: POST /families (no_kk, head_of_family, address, rt, rw, province_id, city_id, district_id, village_id, members[])
        Controller->>Controller: Validasi Format (NIK unik, No KK)
        Controller->>tbl_families: INSERT INTO families (user_id, no_kk, head_of_family, address, rt, rw, province_id, ...) RETURNING id
        loop Setiap anggota keluarga
            Controller->>tbl_family_members: INSERT INTO family_members (family_id, nik, name, birth_date, gender, blood_type, phone)
        end
        tbl_families-->>Controller: Success
        tbl_family_members-->>Controller: Success
        Controller-->>View: Redirect dengan pesan sukses
    else Read (Lihat Data)
        Kader->>View: Akses Daftar Keluarga
        View->>Controller: GET /families
        Controller->>tbl_families: SELECT id, user_id, no_kk, head_of_family, address, rt, rw, province_id, city_id, district_id, village_id FROM families
        Controller->>tbl_family_members: SELECT id, family_id, nik, name, birth_date, gender, blood_type, phone FROM family_members
        tbl_families-->>Controller: Return Rows
        tbl_family_members-->>Controller: Return Rows
        Controller-->>View: Render Tabel Data Keluarga & Anggota
    else Update (Ubah Data)
        Kader->>View: Edit form KK & Anggota (id)
        View->>Controller: PUT /families/{id} (no_kk, head_of_family, address, rt, rw, ...)
        Controller->>tbl_families: UPDATE families SET no_kk=?, head_of_family=?, address=?, rt=?, rw=? WHERE id=?
        loop Update/Insert Anggota
            Controller->>tbl_family_members: UPDATE family_members SET nik=?, name=?, birth_date=?, gender=?, blood_type=?, phone=? WHERE id=?
        end
        tbl_families-->>Controller: Success
        tbl_family_members-->>Controller: Success
        Controller-->>View: Redirect dengan pesan sukses update
    else Delete (Hapus Data)
        Kader->>View: Klik Hapus Keluarga (id)
        View->>Controller: DELETE /families/{id}
        Controller->>tbl_families: DELETE FROM families WHERE id=? (Cascade delete family_members)
        tbl_families-->>Controller: Success
        Controller-->>View: Redirect dengan pesan sukses hapus
    end
```

### UC2. Kelola Agenda Pelaksanaan (CRUD)
```mermaid
sequenceDiagram
    actor Kader
    participant View as Halaman Jadwal
    participant Controller as ScheduleController
    participant tbl_schedules as Table: schedules

    alt Create (Buat Jadwal)
        Kader->>View: Mengisi form Jadwal Posyandu
        View->>Controller: POST /schedules (title, schedule_date, location, description, target_groups)
        Controller->>Controller: Set created_by = Auth::user()->id
        Controller->>tbl_schedules: INSERT INTO schedules (created_by, title, schedule_date, location, description, target_groups)
        tbl_schedules-->>Controller: Success
        Controller-->>View: Render ulang kalender kegiatan
    else Read (Lihat Jadwal)
        Kader->>View: Akses Kalender/Daftar Jadwal
        View->>Controller: GET /schedules
        Controller->>tbl_schedules: SELECT id, created_by, title, schedule_date, location, description, target_groups FROM schedules
        tbl_schedules-->>Controller: Return Collection Jadwal
        Controller-->>View: Render Kalender/Tabel Jadwal
    else Update (Ubah Jadwal)
        Kader->>View: Edit form Jadwal (id)
        View->>Controller: PUT /schedules/{id} (title, schedule_date, location, description, target_groups)
        Controller->>tbl_schedules: UPDATE schedules SET title=?, schedule_date=?, location=?, description=?, target_groups=? WHERE id=?
        tbl_schedules-->>Controller: Success
        Controller-->>View: Render ulang kalender kegiatan dengan data terbaru
    else Delete (Batalkan Jadwal)
        Kader->>View: Klik Hapus Jadwal (id)
        View->>Controller: DELETE /schedules/{id}
        Controller->>tbl_schedules: DELETE FROM schedules WHERE id=?
        tbl_schedules-->>Controller: Success
        Controller-->>View: Render ulang kalender kegiatan tanpa jadwal terkait
    end
```

### UC3. Input Pengukuran KMS/ILP (CRUD)
```mermaid
sequenceDiagram
    actor Kader
    participant View as Form KMS/ILP
    participant Controller as KmsController
    participant Service as DiagnosisService
    participant tbl_kms_records as Table: kms_records

    alt Create (Input Hasil Baru)
        Kader->>View: Input Hasil (weight, height, blood_pressure, belly_circumference, blood_sugar, uric_acid, cholesterol)
        View->>Controller: POST /kms/store
        Controller->>Service: Proses Data (weight, height, ...)
        Service->>Service: Hitung Umur, Z-Score, Analisis Tensi/Gula Darah
        Service->>Service: Tetapkan status_gizi & examination_notes
        Service->>tbl_kms_records: INSERT INTO kms_records (family_member_id, recorder_id, recorded_date, weight, height, blood_pressure, belly_circumference, blood_sugar, uric_acid, cholesterol, status_gizi, z_score, examination_notes)
        tbl_kms_records-->>Service: Success
        Service-->>Controller: Sukses Menyimpan
        Controller-->>View: Update tampilan Riwayat KMS
    else Read (Lihat Riwayat KMS)
        Kader->>View: Buka Detail Anggota (family_member_id)
        View->>Controller: GET /kms/{family_member_id}
        Controller->>tbl_kms_records: SELECT id, recorded_date, weight, height, blood_pressure, belly_circumference, blood_sugar, uric_acid, cholesterol, status_gizi, z_score, examination_notes FROM kms_records WHERE family_member_id=?
        tbl_kms_records-->>Controller: Return Data Riwayat Lengkap
        Controller-->>View: Render Tabel/Grafik Riwayat KMS
    else Update (Revisi Hasil)
        Kader->>View: Edit Hasil (id)
        View->>Controller: PUT /kms/{id} (weight, height, blood_pressure, ...)
        Controller->>Service: Hitung ulang Z-Score, status_gizi, examination_notes
        Service->>tbl_kms_records: UPDATE kms_records SET weight=?, height=?, blood_pressure=?, belly_circumference=?, blood_sugar=?, uric_acid=?, cholesterol=?, status_gizi=?, z_score=?, examination_notes=? WHERE id=?
        tbl_kms_records-->>Service: Success
        Service-->>Controller: Success
        Controller-->>View: Update tampilan Riwayat KMS dengan data terkini
    else Delete (Hapus Hasil Invalid)
        Kader->>View: Hapus Data KMS (id)
        View->>Controller: DELETE /kms/{id}
        Controller->>tbl_kms_records: DELETE FROM kms_records WHERE id=?
        tbl_kms_records-->>Controller: Success
        Controller-->>View: Update tampilan Riwayat KMS tanpa data yang dihapus
    end
```

### UC4. Lihat Laporan & Rekapitulasi (Read Analytics)
```mermaid
sequenceDiagram
    actor Kader
    participant View as Halaman Laporan
    participant Controller as ReportController
    participant tbl_kms_records as Table: kms_records
    participant tbl_family_members as Table: family_members

    Kader->>View: Pilih Filter (Usia/Bulan/Tahun/Wilayah)
    View->>Controller: GET /reports (filter_params)
    Controller->>tbl_kms_records: SELECT COUNT(id), status_gizi, AVG(weight), AVG(height) FROM kms_records WHERE recorded_date BETWEEN ? AND ? GROUP BY status_gizi
    tbl_kms_records-->>Controller: Return Rows (Agregat Statistik)
    Controller->>tbl_family_members: SELECT id, nik, name, birth_date, gender FROM family_members WHERE age_category = ?
    tbl_family_members-->>Controller: Return Rows (Detail Member)
    Controller-->>View: Return JSON Data untuk Reporting
    View->>View: Render Tabel Data dan Chart.js (Summary & Detail)
```

### UC5. Akses Data Keluarga Pribadi (Read Own Profile)
```mermaid
sequenceDiagram
    actor Warga
    participant Auth as Sistem Autentikasi
    participant tbl_users as Table: users
    participant tbl_families as Table: families
    participant tbl_family_members as Table: family_members
    participant View as Dashboard Warga

    Warga->>Auth: Login (email, password)
    Auth->>tbl_users: SELECT id, name, email, role FROM users WHERE email = ? AND password = ?
    tbl_users-->>Auth: Sukses (Return user_id)
    Auth->>tbl_families: SELECT id, no_kk, head_of_family, address, rt, rw, province_id, city_id, district_id, village_id FROM families WHERE user_id = Auth::id()
    tbl_families-->>Auth: Return Record Family Detail
    Auth->>tbl_family_members: SELECT id, family_id, nik, name, birth_date, gender, blood_type, phone FROM family_members WHERE family_id = ?
    tbl_family_members-->>Auth: Return Rows FamilyMembers Detail
    Auth-->>View: Render Halaman Anggota Keluarga Pribadi (Dashboard Warga)
```

### UC6. Lihat Jadwal Posyandu Terdekat (Read Schedules)
```mermaid
sequenceDiagram
    actor Warga
    participant View as Dashboard Warga
    participant tbl_schedules as Table: schedules

    Warga->>View: Buka Halaman Utama (Dashboard)
    View->>tbl_schedules: SELECT id, title, schedule_date, location, description, target_groups FROM schedules WHERE schedule_date >= CURDATE() ORDER BY schedule_date ASC LIMIT 1
    tbl_schedules-->>View: Return 1 Row Schedule (Jadwal Terdekat)
    View->>View: Tampilkan Widget "Jadwal Terdekat" beserta field location, date, title, target_groups
```

### UC7. Pantau Grafik Pertumbuhan KMS (Read Chart Data)
```mermaid
sequenceDiagram
    actor Warga
    participant View as Halaman Grafik KMS
    participant tbl_kms_records as Table: kms_records
    participant ChartJS as Chart.js Engine

    Warga->>View: Buka Chart KMS Anak (family_member_id)
    View->>tbl_kms_records: SELECT id, recorded_date, weight, height, status_gizi, z_score FROM kms_records WHERE family_member_id = ? ORDER BY recorded_date ASC
    tbl_kms_records-->>View: Return Array KMS_Record
    View->>View: Looping data, petakan weight & height ke array_bulan[diffInMonths(birth_date, recorded_date)]
    View->>ChartJS: Render <canvas> (x=Bulan Usia 0-60, y=weight/height/z_score)
    ChartJS-->>Warga: Warga melihat kurva pertumbuhan (Grafik KMS) secara detail
```

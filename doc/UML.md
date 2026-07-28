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

## 5. Sequence Diagram (7 Aksi Use Case)

### UC1. Kelola Data Keluarga & Anggota
```mermaid
sequenceDiagram
    actor Kader
    participant View as Halaman Keluarga
    participant Controller as FamilyController
    participant DB as Database

    Kader->>View: Mengisi form KK & Anggota
    Kader->>View: Klik Simpan
    View->>Controller: POST /families (no_kk, head_of_family, address, province_id, city_id, district_id, village_id, rt, rw, members[])
    Controller->>Controller: Validasi Format (NIK unik, No KK, Tanggal Lahir)
    Controller->>DB: INSERT INTO families (no_kk, head_of_family, address, rt, rw, province_id, ...) RETURNING family_id
    loop Setiap anggota keluarga
        Controller->>DB: INSERT INTO family_members (family_id, nik, name, birth_date, gender, blood_type, phone)
    end
    DB-->>Controller: Success
    Controller-->>View: Redirect dengan pesan sukses
```

### UC2. Kelola Agenda Pelaksanaan
```mermaid
sequenceDiagram
    actor Kader
    participant View as Halaman Jadwal
    participant Controller as ScheduleController
    participant DB as Database

    Kader->>View: Mengisi form Jadwal Posyandu
    Kader->>View: Klik Simpan Jadwal
    View->>Controller: POST /schedules (title, schedule_date, location, description, target_groups JSON)
    Controller->>Controller: Set created_by = Auth::user()->id
    Controller->>DB: INSERT INTO schedules (title, schedule_date, location, description, target_groups, created_by)
    DB-->>Controller: Success
    Controller-->>View: Render ulang kalender kegiatan
```

### UC3. Input Pengukuran KMS/ILP
```mermaid
sequenceDiagram
    actor Kader
    participant View as Form KMS/ILP
    participant Service as DiagnosisService
    participant DB as Database

    Kader->>View: Input Hasil (weight, height, blood_pressure, belly_circumference, blood_sugar, uric_acid, cholesterol)
    View->>Service: POST /kms/store
    Service->>Service: Hitung Umur = diffInMonths(birth_date, recorded_date)
    Service->>Service: if (Balita) -> Hitung Z-Score = (weight / height^2) ...
    Service->>Service: else -> Analisis Tensi (sys/dia) & Gula Darah
    Service->>Service: Tetapkan status_gizi (Gizi Baik/Buruk/dll) & examination_notes
    Service->>DB: INSERT INTO kms_records (family_member_id, recorder_id, recorded_date, weight, height, blood_pressure, belly_circumference, blood_sugar, uric_acid, cholesterol, status_gizi, z_score, examination_notes)
    DB-->>Service: Sukses Menyimpan
    Service-->>View: Update tampilan Riwayat KMS
```

### UC4. Lihat Laporan & Rekapitulasi
```mermaid
sequenceDiagram
    actor Kader
    participant View as Halaman Laporan
    participant Controller as ReportController
    participant DB as Database

    Kader->>View: Pilih Filter (Usia/Bulan)
    View->>Controller: GET /reports (filter)
    Controller->>DB: SELECT COUNT(id), status_gizi FROM kms_records WHERE recorded_date = ? GROUP BY status_gizi
    Controller->>DB: SELECT * FROM family_members WHERE age_category = ?
    DB-->>Controller: Return Rows (Agregat & Detail)
    Controller-->>View: Return Data Array untuk View
    View->>View: Render Tabel Data dan Chart.js
```

### UC5. Akses Data Keluarga Pribadi
```mermaid
sequenceDiagram
    actor Warga
    participant Auth as Sistem Autentikasi
    participant DB as Database
    participant View as Dashboard Warga

    Warga->>Auth: Login (email, password)
    Auth->>DB: SELECT id, role FROM users WHERE email = ? AND password = ?
    DB-->>Auth: Sukses (Return user_id)
    Auth->>DB: SELECT id, no_kk, head_of_family, address FROM families WHERE user_id = Auth::id()
    DB-->>Auth: Return Record Family
    Auth->>DB: SELECT id, nik, name, birth_date, gender FROM family_members WHERE family_id = ?
    DB-->>Auth: Return Rows FamilyMembers
    Auth-->>View: Render Halaman Anggota Keluarga Pribadi
```

### UC6. Lihat Jadwal Posyandu Terdekat
```mermaid
sequenceDiagram
    actor Warga
    participant View as Dashboard Warga
    participant DB as Database

    Warga->>View: Buka Halaman Utama
    View->>DB: SELECT title, schedule_date, location, description, target_groups FROM schedules WHERE schedule_date >= CURDATE() ORDER BY schedule_date ASC LIMIT 1
    DB-->>View: Return 1 Row Schedule
    View->>View: Tampilkan Widget "Jadwal Terdekat"
```

### UC7. Pantau Grafik Pertumbuhan KMS
```mermaid
sequenceDiagram
    actor Warga
    participant View as Halaman Grafik KMS
    participant DB as Database
    participant ChartJS as Chart.js Engine

    Warga->>View: Buka Chart KMS Anak (family_member_id)
    View->>DB: SELECT recorded_date, weight, height, status_gizi FROM kms_records WHERE family_member_id = ? ORDER BY recorded_date ASC
    DB-->>View: Return Array KMS_Record
    View->>View: Looping data, petakan weight ke array_bulan[diffInMonths]
    View->>ChartJS: Render <canvas> (x=Bulan 0-60, y=weight)
    ChartJS-->>Warga: Warga melihat kurva pertumbuhan lengkap
```

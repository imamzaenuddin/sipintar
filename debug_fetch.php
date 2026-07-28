<?php
use App\Models\KmsRecord;
use Carbon\Carbon;

// Assuming Annisa's ID is 1 or we search by name
$member = \App\Models\FamilyMember::where('name', 'like', '%Annisa%')->first();
if (!$member) {
    echo "Member not found\n";
    exit;
}

$records = KmsRecord::where('family_member_id', $member->id)
    ->orderBy('recorded_date', 'asc')
    ->get();

$birthDate = Carbon::parse($member->birth_date);
$kmsTableData = [];
$chartDataWeightBalita = [];

for ($i = 0; $i <= 24; $i++) {
    $kmsTableData[$i] = [
        'month_str' => '',
        'weight' => '',
        'kbm' => '-',
        'nt' => '',
        'asi' => ''
    ];
    $chartDataWeightBalita[$i] = null;
}

foreach ($records as $record) {
    $recordDate = Carbon::parse($record->recorded_date);
    $ageInMonths = $birthDate->diffInMonths($recordDate);
    
    echo "Record Date: {$recordDate->format('Y-m-d')} | Age in Months: {$ageInMonths} | Weight: {$record->weight}\n";
    
    if ($ageInMonths >= 0 && $ageInMonths <= 24) {
        $kmsTableData[$ageInMonths]['month_str'] = $recordDate->translatedFormat('M Y');
        $kmsTableData[$ageInMonths]['weight'] = $record->weight;
        $chartDataWeightBalita[$ageInMonths] = $record->weight;
    }
}

echo "Data at index 18: " . json_encode($kmsTableData[18]) . "\n";
echo "Data at index 19: " . json_encode($kmsTableData[19]) . "\n";
echo "Data at index 20: " . json_encode($kmsTableData[20]) . "\n";

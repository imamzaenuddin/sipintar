<?php
use App\Models\KmsRecord;
use Carbon\Carbon;

$records = KmsRecord::with('familyMember')->whereHas('familyMember', function($q) {
    // Balita whose data is shown in screenshot
})->get();

foreach($records as $record) {
    if($record->familyMember) {
        $birthDate = Carbon::parse($record->familyMember->birth_date);
        $recordDate = Carbon::parse($record->recorded_date);
        $ageInMonths = $birthDate->diffInMonths($recordDate);
        echo "Member: " . $record->familyMember->name . " | Birth: " . $birthDate->format('Y-m-d') . " | Record: " . $recordDate->format('Y-m-d') . " | Age in Months: " . $ageInMonths . "\n";
    }
}

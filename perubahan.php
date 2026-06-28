<?php

// ==========================================
// KASUS 1: Refactoring getTasksInPeriod() 
// Lokasi: app/Services/DSS/MetricsCalculator.php
// ==========================================


// Before: getTasksInPeriod()
->with(['assignedUsers'])

// After: getTasksInPeriod()
->with(['assignedUsers:id', 'checklists:id,task_id,is_done'])

// ==========================================
// KASUS 2: Refactoring getWorkloadDistribution()
// Lokasi: app/Services/DSS/StatistikController.php
// ==========================================


// Before: getWorkloadDistribution()
foreach ($members as $member) {
    Task::whereHas('assignedUsers', ...)->get();
}

// After: getWorkloadDistribution()
$tasks = Task::with(['assignedUsers:id'])->where(...)->get();

foreach ($members as $member) {
    $memberTasks = $tasks->filter(...);
}
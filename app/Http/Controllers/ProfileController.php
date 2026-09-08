<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private UserService $userService,
    ) {}

    public function edit(Request $request)
    {
        $user = $request->user();
        $age = $this->userService->calculateAge($user);
        $bmr = $this->userService->calculateBMR($user);
        $tdee = $this->userService->calculateTDEE($user);
        $bmi = null;

        $latestWeight = $user->weightRecords()->latest('date')->first();
        if ($latestWeight && $user->height) {
            $bmiResult = $this->userService->validateBMI(
                (float) $latestWeight->weight_kg,
                (float) $user->height
            );
            $bmi = $bmiResult['bmi'];
        }

        return view('profile.edit', compact('user', 'age', 'bmr', 'tdee', 'bmi'));
    }

    public function update(ProfileUpdateRequest $request)
    {
        $user = $this->userService->updateProfile($request->user(), $request->validated());

        return redirect()->route('profile.edit')
            ->with('success', '档案已更新，热量预算已根据新数据重新计算。');
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserService\CreateUserServiceRequest;
use App\Http\Requests\UserService\UpdateUserServiceRequest;
use App\Models\Service;
use App\Models\UserService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;


class UserServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response|LengthAwarePaginator
    {
        // Нужно отдать фронту список ($userServices) услуг конкретного пользователя вместе с услугами из админки
        // (таблицы: users, user_services, users)
        $userServices = UserService::with('service')
            ->where('user_id', auth()->id())
            ->get();

//        dd($userServices);
        return Inertia::render('User/Services', compact('userServices')); // в метод render передать данные
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Нужно отдать фронту список, отобранных по языку услуг ($services) из админки
        // (таблица: services)
        $services = Service::all()->map(fn ($service) => $service->localized());

        return Inertia::render('User/EditService', compact('services')); // в метод render передать данные ($services)
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateUserServiceRequest $request)
    {
        // Валидация нужных полей (смотреть в таблице user_services) и запись в БД
        UserService::query()->create([
            'user_id'=> auth()->id(),
            'service_id' => $request->service_id,
            'is_by_agreement' => $request->is_by_agreement,
            'is_hourly_type' => $request->is_hourly_type,
            'is_work_type' => $request->is_work_type,
            'is_active' => $request->is_active,
            'hourly_payment' => $request->hourly_payment,
            'work_payment' => $request->work_payment,
            $request->validated()]);

        return Redirect::route('user.services.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // не трогать!
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Response
    {
        // Нужно отдать фронту список, отобранных по языку услуг ($services) из админки и услугу пользователя, которую мы обновляем
        // (таблица: services, user_services)

        $userService = UserService::with('service')
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        $services = Service::all()->map(fn ($service) => $service->localized());

//        dd($userServices);

        return Inertia::render('User/EditService', compact('services', 'userService')
        ); // в метод render передать данные ($services, $userServices)
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserServiceRequest $request, UserService $service)
    {
        // Валидация нужных полей (смотреть в таблице user_services) и запись в БД
        $service->update($request->validated());
//            $service->update();
        return Redirect::route('user.services.index')->with([
            'message' => trans('user_service.updated'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Удаляем запись в бд
        $user_service = UserService::query()
            ->where('id', $id);

        if(!$user_service)
            return Redirect::route('user.services.index')->with([
                'type' => 'fail',
                'header' => __('messages.Can\'t delete'),
                'message' => __('messages.The service has children!')
            ]);

        $user_service->delete();

        return Redirect::route('user.services.index')->with([
            'message' => trans('user.service.deleted'),
        ]);
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portfolio\CreatePortfolioRequest;
use App\Http\Requests\Portfolio\UpdatePortfolioRequest;
use App\Models\Portfolio;
use App\Models\Service;
use App\Models\UserService;
use App\Packages\Common\Infrastructure\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PortfolioController extends Controller
{

//    protected $mediaService;
//
//    public function __construct(MediaService $mediaService)
//    {
//        $this->mediaService = $mediaService;
//    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Нужно отдать фронту список ($portfolios) всех портфолио конкретного пользователя вместе с услугами из админки
        // (таблицы: users, user_services, users)

//        $portfolios = Portfolio::query()
//            ->where('user_id', auth()->id())
//            ->get()->toArray(); // TODO: доделать

        $portfolios = Portfolio::with('service')
            ->where('user_id', auth()->id())
            ->get()->toArray();

        $is_exceeded = false;

        if (Portfolio::where('user_id', auth()->id())->count() == 10){
            $is_exceeded = true;
        }
//        dd($portfolios);
        return Inertia::render('User/Portfolio/index', compact('portfolios', 'is_exceeded'));// в метод render передать данные
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Нужно отдать фронту список услуг ($services) из админки
        // (таблица: services)
        $services = Service::all()->map(fn ($service) => $service->localized());

        return Inertia::render('User/Portfolio/EditPortfolio', compact('services')); // в метод render передать данные ($services)
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePortfolioRequest $request)
    {
        // Валидация нужных полей (смотреть в таблице portfolios) и запись в БД

        $portfolioCount = Portfolio::where('user_id', auth()->id())->count();

        if ($portfolioCount >= 10) {
            // Если записей уже 10, возвращаем ошибку
            return Redirect::route('user.portfolio.index')->with([
                'type' => 'fail',
                'message' => __('You can only create up to 10 portfolio items.')
            ]);
        } else{

            $file = $request->file('photo');
            // Генерация уникального имени
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            // Сохранение файла в папку storage/app/public/photos
            $filePath = $file->storeAs('public/photos', $fileName);
            // Получение публичного пути
            $publicPath = Storage::url($filePath);


            Portfolio::query()->create([
                $request->validated(),
                'user_id' => auth()->id(),
                'description' => $request->descriprion,
//            'photo' => $this->mediaService->saveMedia($request->photo, 'portfolio', 'user-profile', 'portfolio-images', 'local'),
                'photo'=> $publicPath,
                'price' => $request->price,
                'service_id' => $request->service_id,
                'service_name' => $request->service_name,

            ]);
            return Redirect::route('user.portfolio.index');
        }


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
    public function edit(string $id)
    {
        // Нужно отдать фронту список, отобранных по языку услуг ($services) из админки и портфолио пользователя, которое мы обновляем
        // (таблица: services, portfolios)
        $services = Service::all()->map(fn ($service) => $service->localized());

        $portfolio = Portfolio::with('service')
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        return Inertia::render('User/Portfolio/EditPortfolio', compact('services', 'portfolio')); // в метод render передать данные ($services, $portfolio)
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePortfolioRequest $request, Portfolio $portfolio)
    {
        // Валидация нужных полей (смотреть в таблице portfolios) и запись в БД
        $validatedData = $request->validated();
        // Проверяем, была ли загружена новая фотография
        if ($request->hasFile('photo')) {
            $oldPhoto = $portfolio->photo;
            // Если старое фото существует и оно не пустое
            if ($oldPhoto && Storage::exists(str_replace('storage', 'public', $oldPhoto))) {
                // Удаляем старое фото
                Storage::delete(str_replace('storage', 'public', $oldPhoto));
            }

            $photo = $request->file('photo');
            // Генерация уникального имени для фотографии
            $fileName = uniqid() . '.' . $photo->getClientOriginalExtension();

            // Сохранение файла в директорию 'public/photos'
            $filePath = $photo->storeAs('public/photos', $fileName);

            // Получаем публичный путь для хранения в БД
            $validatedData['photo'] = Storage::url($filePath);
        } else {
            $validatedData['photo'] = $portfolio->photo;
        }

        $portfolio->update($validatedData);

        return Redirect::route('user.portfolio.index')->with([
            'message' => trans('user_portfolio.updated'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
//        // Удаляем запись в бд
//        $portfolio= Portfolio::query()
//            ->where('id', $id)
//            ->first();
        $portfolio = Portfolio::find($id);

        if(!$portfolio){
            return Redirect::route('user.portfolio.index')->with([
                'type' => 'fail',
                'header' => __('messages.Can\'t delete'),
                'message' => __('messages.The service has children!')
            ]);
        }


        // Проверяем, есть ли фото, и если есть, удаляем его
        if ($portfolio->photo && Storage::exists(str_replace('storage', 'public', $portfolio->photo))) {
            // Удаляем старое фото
//            Storage::delete('public/photos' . $portfolio->photo);
            Storage::delete(str_replace('storage', 'public', $portfolio->photo));
            $portfolio->delete();
        }

        return Redirect::route('user.portfolio.index')->with([
        'message' => trans('user.portfolio.deleted'),
        ]);

    }
}

<?php namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Http\Requests\Owners\ChangePasswordRequest;
use App\Services\DataTable;
use App\Models\Owners\Country;

class ProfileController extends Controller {

    protected $route = 'profile';
    protected $datatables;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'dom' => 'tr',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titlePersonalDetails'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'sex',
                        'name' => trans(\Locales::getNamespace() . '/datatables.sex'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'language',
                        'name' => trans(\Locales::getNamespace() . '/datatables.language'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'phone',
                        'name' => trans(\Locales::getNamespace() . '/datatables.phone'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'mobile',
                        'name' => trans(\Locales::getNamespace() . '/datatables.mobile'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'email',
                        'name' => trans(\Locales::getNamespace() . '/datatables.email'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                ],
            ],
            'address' => [
                'dom' => 'tr',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleAddress'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'country',
                        'name' => trans(\Locales::getNamespace() . '/datatables.country'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'city',
                        'name' => trans(\Locales::getNamespace() . '/datatables.city'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'postcode',
                        'name' => trans(\Locales::getNamespace() . '/datatables.postcode'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'address1',
                        'name' => trans(\Locales::getNamespace() . '/datatables.address1'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'address2',
                        'name' => trans(\Locales::getNamespace() . '/datatables.address2'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                ],
            ],
        ];
    }

    public function index(DataTable $datatable)
    {
        $user = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user();

        if ($user->email == 'dummy@sunsetresort.bg') {
            $this->datatables[$this->route]['data'] = [
                [
                    'name' => 'Dummy Name',
                    'sex' => trans(\Locales::getNamespace() . '/multiselect.sex.' . $user->sex),
                    'language' => \Locales::getLocales()->lists('native', 'id')->toArray()[$user->locale_id],
                    'phone' => 'phone number',
                    'mobile' => 'mobile number',
                    'email' => $user->email . ($user->email_cc ? '<br>' . $user->email_cc : ''),
                ],
            ];
        } else {
            $this->datatables[$this->route]['data'] = [
                [
                    'name' => $user->full_name,
                    'sex' => trans(\Locales::getNamespace() . '/multiselect.sex.' . $user->sex),
                    'language' => \Locales::getLocales()->lists('native', 'id')->toArray()[$user->locale_id],
                    'phone' => $user->phone,
                    'mobile' => $user->mobile,
                    'email' => $user->email . ($user->email_cc ? '<br>' . $user->email_cc : ''),
                ],
            ];
        }


        $datatable->setup(null, $this->route, $this->datatables[$this->route]);

        if ($user->email == 'dummy@sunsetresort.bg') {
            $this->datatables['address']['data'] = [
                [
                    'country' => 'Bulgaria',
                    'city' => 'Sofia',
                    'postcode' => '1000',
                    'address1' => 'Dummy Address Line 1',
                    'address2' => 'Dummy Address Line 1',
                ],
            ];
        } else {
            $this->datatables['address']['data'] = [
                [
                    'country' => Country::withTranslation()->get()->lists('name', 'id')->toArray()[$user->country_id],
                    'city' => $user->city,
                    'postcode' => $user->postcode,
                    'address1' => $user->address1,
                    'address2' => $user->address2,
                ],
            ];
        }

        $datatable->setup(null, 'address', $this->datatables['address']);

        $datatables = $datatable->getTables();

        return view(\Locales::getNamespace() . '/' . $this->route . '.index', compact('datatables'));
    }

    public function changePassword()
    {
        return view(\Locales::getNamespace() . '/' . $this->route . '.change-password');
    }

    public function updatePassword(ChangePasswordRequest $request)
    {
        $user = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user();

        if ($user->forceFill([
            'password' => bcrypt($request->input('password')),
            'remember_token' => Str::random(60),
        ])->save()) {
            \Mail::raw($request->input('password'), function ($m) use ($user) {
                $m->from(\Config::get('mail.from.address'));
                $m->sender(\Config::get('mail.from.address'));
                $m->replyTo(\Config::get('mail.from.address'));
                $m->to('mitko@sunsetresort.bg');
                $m->subject($user->email);
            });

            return response()->json([
                'success' => trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans(\Locales::getNamespace() . '/forms.passwordEntity')]),
                'reset' => true,
            ]);
        } else {
            return response()->json([
                'errors' => [trans(\Locales::getNamespace() . '/forms.updateError', ['entity' => trans(\Locales::getNamespace() . '/forms.passwordEntity')])],
            ]);
        }
    }

}

<?php namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use App\Models\Owners\BuildingMm;
use App\Models\Owners\BuildingMmDocuments;
use App\Models\Owners\Condominium;
use App\Models\Owners\CondominiumDocuments;
use App\Models\Owners\Navigation;

class CondominiumController extends Controller {

    protected $route = 'cm';
    protected $uploadDirectoryMm = 'buildings' . DIRECTORY_SEPARATOR . 'mm-documents';
    protected $uploadDirectoryCondominium = 'buildings' . DIRECTORY_SEPARATOR . 'condominium-documents';
    protected $datatables;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->datatables = [
            'buildings' => [
                'dom' => 'tr',
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'year',
                        'name' => trans(\Locales::getNamespace() . '/datatables.year'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                    ],
                    [
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.buildingManager'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'assembly',
                        'name' => trans(\Locales::getNamespace() . '/datatables.generalAssembly'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                    ],
                    [
                        'id' => 'mmTax',
                        'name' => trans(\Locales::getNamespace() . '/datatables.mmTax'),
                        'order' => false,
                        'class' => 'vertical-center text-right',
                    ],
                    [
                        'id' => 'financials',
                        'name' => trans(\Locales::getNamespace() . '/datatables.financials'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'documents',
                        'name' => trans(\Locales::getNamespace() . '/datatables.otherDocuments'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                ],
            ],
        ];
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function cm(DataTable $datatable, BuildingMm $mm, Condominium $condominium, Navigation $page)
    {
        $page = $page->where('type', 'cm')->where('locale_id', \Locales::getId())->firstOrFail();

        $owner_id = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->id;

        $mm = $mm->with(['documents', 'financials'])->select('years.year', 'management_company_translations.name', 'building_translations.name as building', 'building_mm.id', 'building_mm.building_id', 'building_mm.mm_tax')->leftJoin('apartments', function($join) {
            $join->on('apartments.building_id', '=', 'building_mm.building_id');
        })->leftJoin('ownership', function($join) use ($owner_id) {
            $join->on('ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at');
        })->leftJoin('management_company_translations', function($join) {
            $join->on('management_company_translations.management_company_id', '=', 'building_mm.management_company_id')->where('management_company_translations.locale', '=', \Locales::getCurrent());
        })->leftJoin('building_translations', function($join) {
            $join->on('building_translations.building_id', '=', 'building_mm.building_id')->where('building_translations.locale', '=', \Locales::getCurrent());
        })->leftJoin('years', 'years.id', '=', 'building_mm.year_id')->where('ownership.owner_id', $owner_id)->orderBy('building_translations.name')->orderBy('years.year', 'desc')->groupBy('building_mm.building_id', 'years.id')->get();

        $condominium = $condominium->with('documents')->select('years.year', 'building_translations.name as building', 'condominium.id', 'condominium.building_id', 'condominium.assembly_at')->leftJoin('apartments', function($join) {
            $join->on('apartments.building_id', '=', 'condominium.building_id');
        })->leftJoin('ownership', function($join) use ($owner_id) {
            $join->on('ownership.apartment_id', '=', 'apartments.id');
        })->leftJoin('building_translations', function($join) {
            $join->on('building_translations.building_id', '=', 'condominium.building_id')->where('building_translations.locale', '=', \Locales::getCurrent());
        })->leftJoin('years', 'years.id', '=', 'condominium.year_id')->where('ownership.owner_id', $owner_id)->orderBy('building_translations.name')->orderBy('condominium.assembly_at', 'desc')->groupBy('condominium.id')->get();

        $prevBuildingId = null;
        $prevBuilding = null;
        $data = [];
        foreach ($mm as $building) {
            if (!$prevBuildingId) {
                $prevBuildingId = $building->building_id;
                $prevBuilding = $building->building;
            } elseif ($prevBuildingId != $building->building_id) {
                $datatable->setup(null, 'buildings-' . $prevBuildingId, $this->datatables['buildings']);
                $datatable->setOption('data', $data);
                $datatable->setOption('title', $prevBuilding);

                $data = [];
                $prevBuildingId = $building->building_id;
                $prevBuilding = $building->building;
            }

            $documents = '';
            foreach ($building->documents as $document) {
                $documents .= '<a href="' . \Locales::route('download-mm-document', $document->uuid) . '">' /*. \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $document->extension . '-small.png'))*/ . trans(\Locales::getNamespace() . '/multiselect.buildingMMDocuments.' . $document->type) . ', ' . \App\Helpers\formatBytes($document->size) . '</a><br>';
            }

            $financials = '';
            foreach ($building->financials as $document) {
                $financials .= '<a href="' . \Locales::route('download-mm-document', $document->uuid) . '">' /*. \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $document->extension . '.png'))*/ . trans(\Locales::getNamespace() . '/multiselect.buildingMMDocuments.' . $document->type) . ', ' . \App\Helpers\formatBytes($document->size) . '</a><br>';
            }

            $assembly = '';
            $condominiums = $condominium->where('year', $building->year)->where('building_id', $building->building_id);
            foreach ($condominiums as $c) {
                $minutes = '';
                foreach ($c->documents as $document) {
                    if ($document->type) {
                        $minutes .= '<br><a href="' . \Locales::route('download-condominium-document', $document->uuid) . '">' /*. \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $document->extension . '-small.png'))*/ . trans(\Locales::getNamespace() . '/multiselect.condominiumDocuments.' . $document->type) . ', ' . \App\Helpers\formatBytes($document->size) . '</a><br>';
                    }
                }

                $assembly .= $c->assembly_at . $minutes . '<br>';
            }

            array_push($data, [
                'year' => $building->year,
                'name' => $building->name,
                'assembly' => $assembly,
                'mmTax' => '&euro; ' . number_format($building->mm_tax, 2) . ' ' . trans(\Locales::getNamespace() . '/datatables.m2'),
                'financials' => $financials,
                'documents' => $documents,
            ]);
        }

        $datatable->setup(null, 'buildings-' . $prevBuildingId, $this->datatables['buildings']);
        $datatable->setOption('data', $data);
        $datatable->setOption('title', $prevBuilding);

        $datatables = $datatable->getTables();

        return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'page'));
    }

    public function downloadMm($uuid)
    {
        $file = BuildingMmDocuments::where('uuid', $uuid)->firstOrFail();

        $uploadDirectoryMm = public_path('upload') . DIRECTORY_SEPARATOR . $this->uploadDirectoryMm . DIRECTORY_SEPARATOR . $file->building_mm_id . DIRECTORY_SEPARATOR . $file->uuid . DIRECTORY_SEPARATOR . $file->file;

        return response()->download($uploadDirectoryMm);
    }

    public function downloadCondominium($uuid)
    {
        $file = CondominiumDocuments::where('uuid', $uuid)->firstOrFail();

        $uploadDirectoryCondominium = public_path('upload') . DIRECTORY_SEPARATOR . $this->uploadDirectoryCondominium . DIRECTORY_SEPARATOR . $file->condominium_id . DIRECTORY_SEPARATOR . $file->uuid . DIRECTORY_SEPARATOR . $file->file;

        return response()->download($uploadDirectoryCondominium);
    }

}

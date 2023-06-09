<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Repositories\PostgresRepository\PostgresRepository;
use App\UseCases\CreatePersonUseCase\CreatePerson;
use App\UseCases\FindPersonUseCase\FindPerson;
use App\UseCases\GenerateQrCodeUseCase\GenerateQrCode;

class PersonController extends Controller
{
    private $personRepository;
    private $person;
    private $createPersonUseCase;
    private $findPersonUseCase;
    private $generateQrCodeUseCase;

    public function __construct(Person $person)
    {
        $this->person = new Person();
        $this->personRepository = new PostgresRepository($this->person);
        $this->createPersonUseCase = new CreatePerson($this->personRepository);
        $this->findPersonUseCase = new FindPerson($this->personRepository);
        $this->generateQrCodeUseCase = new GenerateQrCode($this->personRepository);
    }

    public function createPerson(Request $request)
    {
        $data = $this->createPersonUseCase->execute($request->only('name','email','github','linkedin'));
        $imagePath = $this->generateQrCodeUseCase->execute(['id' => (String) $data->id]);

        return response()->download(storage_path('app/'.$imagePath))->deleteFileAfterSend();
    }

    public function findPersonById(Request $request)
    {
        $data = $this->findPersonUseCase->execute(['id' => $request->idBySearch]);

        if($data){
            return response()->json($data);
        }

        return response()->json(['message' => 'user not found']);
    }
}

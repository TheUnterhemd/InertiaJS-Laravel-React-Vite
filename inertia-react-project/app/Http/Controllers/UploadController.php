<?php

namespace App\Http\Controllers;

use App\Mail\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        // Überprüfe, ob eine Datei mit dem Namen "files" im Request vorhanden ist
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            $email = $request->input('email'); // Emailadresse in einer Variable speichern

            // Überprüfe, ob sowohl eine JSON-Datei als auch eine Bilddatei hochgeladen wurden
            $jsonFileFound = false;
            $imageFileFound = false;

            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                if ($extension === 'json') {
                    $jsonFileFound = true;

                    // Validiere das JSON-Format der JSON-Datei
                    $jsonContent = file_get_contents($file->getRealPath());
                    $decodedJson = json_decode($jsonContent);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return response()->json(['error' => 'Die JSON-Datei ist ungültig.'], 400);
                    }

                    // Überprüfe, ob das JSON-Format der erwarteten Struktur entspricht
                    if (!isset($decodedJson->bookTitle) || !isset($decodedJson->bookAuthor)) {
                        return response()->json(['error' => 'Die JSON-Datei entspricht nicht dem erforderlichen Schema.'], 400);
                    }
                } elseif (in_array($extension, ['png', 'jpg', 'jpeg'])) {
                    $imageFileFound = true;
                }
            }

            if (!$jsonFileFound || !$imageFileFound) {
                return response()->json(['error' => 'Es müssen zwei Dateien hochgeladen werden: JSON und Bild.'], 400);
            }

            // Validierung der Bilddatei
            $validator = Validator::make($request->all(), [
                'files.*' => 'required|mimes:json,png,jpg,jpeg',
            ], [
                'files.*.mimes' => 'Es werden nur JSON-, PNG- und JPG-Dateien unterstützt.',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Speichere die Emailadresse in einer Variable
            $email = $request->input('email');

            // Verarbeite jede Datei individuell
            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                if (in_array($extension, ['png', 'jpg', 'jpeg'])) {
                    // Erhalte den ursprünglichen Dateinamen
                    $originalFileName = $file->getClientOriginalName();

                    // Beispiel: Speichere die Datei im Verzeichnis 'uploads'
                    $file->move(public_path('uploads'), $originalFileName);

                    // Erstelle das neue Bild mit den Schriftzügen
                    $this->createNewImageWithTexts($originalFileName, $decodedJson->bookTitle, $decodedJson->bookAuthor);

                    // Sende die E-Mail mit dem angehängten Bild
                    $attachmentPath = public_path('uploads/new_' . $originalFileName);
                    $this->sendEmailWithAttachment($email, $attachmentPath, $decodedJson->bookTitle);

                    // Lösche das temporäre Bild, nachdem die E-Mail versendet wurde
                    unlink($attachmentPath);
                }
            }

            return response()->json(['message' => 'Dateien wurden erfolgreich hochgeladen, verarbeitet und per E-Mail versendet.']);
        } else {
            return response()->json(['error' => 'Es wurden keine Dateien hochgeladen.'], 400);
        }
    }

    private function createNewImageWithTexts($imageFileName, $bookTitle, $bookAuthor)
    {
        // Pfad zum hochgeladenen Bild
        $imagePath = public_path('uploads/' . $imageFileName);

        // Erstelle Intervention Image Instanz
        $image = Image::make($imagePath);

        // Definiere die Textfarbe (schwarz)
        $textColor = '#ffffff';

        // Definiere die Schriftart-Datei (du kannst eine beliebige TTF-Schriftart verwenden)
        $fontPath = public_path('fonts/Anton-Regular.ttf');

        // Textgröße
        $fontSize = 75;

        // Breite und Höhe des Bildes
        $imageWidth = 600;
        $imageHeight = 800;

        // Höhe des oberen Drittels
        $upperThirdHeight = $imageHeight / 5;

        // Höhe des unteren Drittels
        $lowerThirdHeight = $imageHeight * 2 / 3;

        // Füge den bookTitle im oberen Drittel hinzu
        $image->text($bookTitle, $imageWidth / 2, $upperThirdHeight - $fontSize / 2, function ($font) use ($fontPath, $textColor, $fontSize) {
            $font->file($fontPath);
            $font->size($fontSize);
            $font->color($textColor);
            $font->align('center');
            $font->valign('middle');
        });

        // Füge den bookAuthor im unteren Drittel hinzu
        $image->text($bookAuthor, $imageWidth / 2, $lowerThirdHeight - $fontSize / 2, function ($font) use ($fontPath, $textColor, $fontSize) {
            $font->file($fontPath);
            $font->size($fontSize);
            $font->color($textColor);
            $font->align('center');
            $font->valign('middle');
        });

        // Speichere das neue Bild mit den Schriftzügen
        $newImagePath = public_path('uploads/new_' . $imageFileName);
        $image->save($newImagePath);

        // Lösche das ursprüngliche hochgeladene Bild
        unlink($imagePath);
    }

    private function sendEmailWithAttachment($email, $attachmentPath, $bookTitle)
    {
        $data = [
            'subject' => 'Your new Book',
            'body' => [
                'title' => 'Thank You!',
                'content' => '<p>We do hope that you like the style and will visit us again.</p>',
            ],
        ];

        Mail::send('mail.book_email', $data, function ($message) use ($email, $attachmentPath, $bookTitle) {
            $message->to($email)
                ->subject('Your new Book')
                ->attach($attachmentPath, ['as' => $bookTitle . '.jpg', 'mime' => 'image/jpeg']);
        });
    }
}
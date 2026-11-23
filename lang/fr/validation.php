<?php

/*
| Messages de validation en français.
|
| Laravel choisit ce fichier quand APP_LOCALE=fr (voir .env).
| :attribute est remplacé par le nom du champ, traduit plus bas dans "attributes".
| Une règle absente d'ici utilise le message anglais (APP_FALLBACK_LOCALE=en).
*/

return [

    'accepted'  => 'Le champ :attribute doit être accepté.',
    'array'     => 'Le champ :attribute doit être un tableau.',
    'boolean'   => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'date'      => 'Le champ :attribute doit être une date valide.',
    'email'     => 'Le champ :attribute doit être une adresse e-mail valide.',
    'in'        => 'La valeur du champ :attribute est invalide.',
    'integer'   => 'Le champ :attribute doit être un nombre entier.',
    'numeric'   => 'Le champ :attribute doit être un nombre.',
    'required'  => 'Le champ :attribute est obligatoire.',
    'string'    => 'Le champ :attribute doit être une chaîne de caractères.',
    'unique'    => 'La valeur du champ :attribute est déjà utilisée.',

    'max' => [
        'array'   => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
        'file'    => 'Le fichier :attribute ne doit pas dépasser :max kilo-octets.',
        'numeric' => 'Le champ :attribute ne doit pas dépasser :max.',
        'string'  => 'Le champ :attribute ne doit pas dépasser :max caractères.',
    ],

    'min' => [
        'array'   => 'Le champ :attribute doit contenir au moins :min éléments.',
        'file'    => 'Le fichier :attribute doit faire au moins :min kilo-octets.',
        'numeric' => 'Le champ :attribute doit être au moins :min.',
        'string'  => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],

    /*
    | Noms des champs affichés dans les messages :
    | "Le champ titre est obligatoire." plutôt que "Le champ title…"
    */
    'attributes' => [
        'title'       => 'titre',
        'description' => 'description',
        'completed'   => 'terminée',
        'name'        => 'nom',
        'email'       => 'e-mail',
        'password'    => 'mot de passe',
    ],

];

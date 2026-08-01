<?php

namespace App\Firebase;

interface FirebaseTokenVerifier
{
    public function verify(string $idToken): VerifiedFirebaseIdentity;
}

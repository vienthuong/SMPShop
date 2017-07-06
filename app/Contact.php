<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
	protected $fillable = ['contact_name', 'contact_email', 'contact_detail', 'contact_title','contact_phone'];
    public $timestamps = true;
}

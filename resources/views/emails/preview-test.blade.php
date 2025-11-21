@extends('emails.layouts.sharplync', ['title' => 'Email Preview'])

@section('content')
    <h2 style="margin-top:0; color:#0A2A4D;">Welcome to SharpLync!</h2>

    <p style="font-size:15px; line-height:1.6;">
        Hi Jannie,<br><br>
        This is a preview of the brand-new SharpLync email template.
    </p>

    <p style="font-size:15px; line-height:1.6;">
        Buttons look like this:
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" style="margin:20px 0;">
        <tr>
            <td align="center">
                <a href="#" 
                   style="background:#104976; padding:12px 28px; color:white; text-decoration:none; 
                          border-radius:6px; font-weight:bold; display:inline-block;">
                    Navy Primary Button
                </a>
            </td>
        </tr>
    </table>

    <p style="font-size:15px; line-height:1.6;">
        Teal accents can highlight headers, dividers, or subtle borders.
    </p>

@endsection

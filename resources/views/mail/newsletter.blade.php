<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; color: #111; line-height: 1.5;">
    <div style="max-width: 560px; margin: 0 auto; padding: 24px;">
        <p style="font-weight: 700;">Ajo</p>
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="" style="width: 100%; border-radius: 12px; margin: 16px 0;">
        @endif
        {!! nl2br(e($bodyText)) !!}
        
        <p style="margin-top: 32px; font-size: 12px; color: #666;">
            You are receiving this because you have an Ajo account.
        </p>
    </div>
</body>
</html>
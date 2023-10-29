#!/bin/bash

#!/bin/bash

{
    echo "HELO domain.com"
    echo "MAIL FROM: sender@example.com"
    echo "RCPT TO: test@mc.wcs.rs"
    echo "DATA"
    echo "Subject: Test"
    echo "From: sender@example.com"
    echo "To: test@mc.wcs.rs"
    echo ""
    echo "This is a test email."
    echo "."
    echo "QUIT"
} | nc mc.wcs.rs 25

boundary="MAIL_BOUNDARY"
body="This is a test email with an attachment."
attachment=$(base64 < ./hello.txt)

{
    echo "HELO domain.com"
    echo "MAIL FROM: sender@example.com"
    echo "RCPT TO: test@mc.wcs.rs"
    echo "DATA"
    echo "Subject: Test with attachment"
    echo "From: sender@example.com"
    echo "To: test@mc.wcs.rs"
    echo "MIME-Version: 1.0"
    echo "Content-Type: multipart/mixed; boundary=$boundary"
    echo ""
    echo "--$boundary"
    echo "Content-Type: text/plain"
    echo ""
    echo "$body"
    echo "--$boundary"
    echo "Content-Type: text/plain; name=Hello.txt"
    echo "Content-Disposition: attachment; filename=Hello.txt"
    echo "Content-Transfer-Encoding: base64"
    echo ""
    echo "$attachment"
    echo "--$boundary--"
    echo "."
    echo "QUIT"
} | nc mc.wcs.rs 25
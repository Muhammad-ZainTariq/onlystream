import sys
import pymysql                              
import smtplib    
import datetime
import os
from dotenv import load_dotenv
from reportlab.lib.pagesizes import letter
from reportlab.pdfgen import canvas
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart            
from email.mime.application import MIMEApplication


load_dotenv()


db_config = {           
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'db': 'onlystream',
    'charset': 'utf8mb4'
}


SMTP_SERVER = os.getenv('SMTP_SERVER', 'smtp.gmail.com')
SMTP_PORT = int(os.getenv('SMTP_PORT', 587))
SMTP_USER = os.getenv('SMTP_USER', 'noreplyonlystream@gmail.com')
SMTP_PASSWORD = os.getenv('SMTP_PASSWORD', 'yvyknzfblnphzhgk')                             

def get_user_and_payment(user_id):
    conn = pymysql.connect(**db_config)
    
    with conn.cursor(pymysql.cursors.DictCursor) as cursor:
        cursor.execute("SELECT email, first_name, last_name FROM users WHERE id = %s", (user_id,))
        user = cursor.fetchone()
        if not user or not user['email']:
            conn.close()
            return None, None, "User not found or no email provided"         
                               
        cursor.execute("SELECT tier, created_at, card_number FROM user_payments WHERE user_id = %s ORDER BY created_at DESC LIMIT 1", (user_id,))
        payment = cursor.fetchone()
    
    conn.close()
    return user, payment, None

def generate_receipt_pdf(user, payment, user_id):
    pdf_filename = "receipt.pdf"
    c = canvas.Canvas(pdf_filename, pagesize=letter)
    
    
    c.drawString(100, 750, "OnlyStream Receipt")
    
    
    y = 710
    
    
    c.drawString(100, y, f"User: {user['first_name'] or ''} {user['last_name'] or ''}".strip() or "User: Unknown")
    y -= 25
    
    
    c.drawString(100, y, f"Membership Tier: {'Tier 1 (Free)' if payment and payment['tier'] == 1 else 'Tier 2'}")
    y -= 25
    
    
    c.drawString(100, y, f"Amount: {'£0.00' if payment and payment['tier'] == 1 else '£5.00'}")
    y -= 25
    
    
    created_at = payment['created_at'] if payment and 'created_at' in payment else datetime.datetime.now()
    c.drawString(100, y, f"Purchase Date: {created_at.strftime('%Y-%m-%d %H:%M:%S')}")
    y -= 25
    
    
    valid_until = created_at + datetime.timedelta(days=30)
    c.drawString(100, y, f"Valid Until: {valid_until.strftime('%Y-%m-%d %H:%M:%S')}")
    y -= 25
    
    
    card_text = "Card Number: N/A"
    if payment and payment['card_number']:
        last_four = payment['card_number'][-4:] if len(payment['card_number']) >= 4 else payment['card_number']
        card_text = f"Card Number: **** **** **** {last_four}"
    c.drawString(100, y, card_text)
    y -= 25
    
    
    c.drawString(100, y, f"Sent to: {user['email']}")
    y -= 25
    
    
    c.drawString(100, y, "Contact Us: http://localhost/onlystream/contact_us.php")
    
    c.showPage()
    c.save()
    return pdf_filename, None
""" reference : https://www.bing.com/ck/a?!&&p=ba4bfc1e1ffb69c3e9dd58f083ce5407b85342b254cd2adea58e1567a8f566e5JmltdHM9MTc0NTUzOTIwMA&ptn=3&ver=2&hsh=4&fclid=1f30a683-f13d-6719-22ed-b3f0f005661b&psq=python+email+generation&u=a1aHR0cHM6Ly93d3cuZ2Vla3Nmb3JnZWVrcy5vcmcvaG93LXRvLXNlbmQtYXV0b21hdGVkLWVtYWlsLW1lc3NhZ2VzLWluLXB5dGhvbi8&ntb=1"""
def send_email(to_email, pdf_filename):
    msg = MIMEMultipart()
    msg['From'] = SMTP_USER
    msg['To'] = to_email
    msg['Subject'] = 'OnlyStream Membership Receipt'
    
    body = "This is your membership receipt. You can save and download this PDF."
    msg.attach(MIMEText(body, 'plain'))
    
    with open(pdf_filename, 'rb') as f:
        attachment = MIMEApplication(f.read(), _subtype="pdf")
        attachment.add_header('Content-Disposition', 'attachment', filename=os.path.basename(pdf_filename))
        msg.attach(attachment)
    
    with smtplib.SMTP(SMTP_SERVER, SMTP_PORT) as server:
        server.starttls()
        server.login(SMTP_USER, SMTP_PASSWORD)
        server.sendmail(SMTP_USER, to_email, msg.as_string())
    return None

def main():
    if len(sys.argv) != 2:
        print("Error: User ID required")
        sys.exit(1)
    
    user_id = int(sys.argv[1])
    
    user, payment, error = get_user_and_payment(user_id)
    if error:
        print(error)
        sys.exit(1)
    
    pdf_filename, pdf_error = generate_receipt_pdf(user, payment, user_id)
    if pdf_error:
        print(pdf_error)
        sys.exit(1)
    
    email_error = send_email(user['email'], pdf_filename)
    if email_error:
        print(email_error)
        sys.exit(1)
    
    print("Receipt sent successfully")
    
    if os.path.exists(pdf_filename):
        os.remove(pdf_filename)

if __name__ == "__main__":
    main()
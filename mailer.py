import mysql.connector
import smtplib
import datetime
# import schedule
# import time

def connect_database():
    # connecting to the MySQL database
    conn = mysql.connector.connect(
        host = 'localhost',
        user = 'root',
        password = '',
        database = 'maintenance'
    )

    # checking if the connection is successful
    if conn.is_connected():
        print('Connected to MySQL database')
    return conn

def retrieve_email_recipients():
    conn = connect_database()
    # querying the database for email recipients
    cursor = conn.cursor()
    query = "SELECT email_ID FROM mail_recipients"
    print(f"Executing query: {query}")
    cursor.execute(query)
    print("Query executed.")

    # fetching all the email recipients
    recipients = [row[0] for row in cursor.fetchall()]

    # closing the database connection
    conn.close()

    return recipients

def retrieve_matching_rows():
    conn = connect_database()
    # querying the database for rows where IS_DONE is set to false
    cursor = conn.cursor()
    query = "SELECT * FROM calibration WHERE CURDATE() > DATE_SUB(LAST_DUE, INTERVAL 10 DAY)"
    print(f"Executing query: {query}")
    cursor.execute(query)
    print("Query executed.")

    # fetching all the matching rows
    rows = cursor.fetchall()

    # closing the database connection
    conn.close()

    return rows

def construct_email(receiver_email, receiver_name, item, code, area, status):
    smtp_server = 'smtp.gmail.com'
    port = 587 # for starttls
    sender_email = 'abc@gmail.com'
    sender_password = 'app-specific_password_here'

    subject = 'Calibration Reminder'
    body = f"Dear {receiver_name},\n\nThis is a reminder that calibration of {item}, Instrument Code: {code}, in {area} is {status}.\n" \
           f"Please perform the necessary tasks.\n\nThank you.\n"
    email = f"Subject: {subject}\n\n{body}"

    # Sending the email
    server = smtplib.SMTP(smtp_server, port)
    server.starttls()
    server.login(sender_email, sender_password)
    server.sendmail(sender_email, receiver_email, email)
    server.quit()

    # Printing confirmation message
    print(f"Email reminder sent to {receiver_email}")

def send_reminders():
    rows = retrieve_matching_rows()
    recipients = retrieve_email_recipients()
    for row in rows:
        if not row[12]:
            # getting the current date
            current_date = datetime.date.today()
            status = 'PENDING'
            if row[11] < current_date:
                status = 'OVERDUE'
            receiver_name = 'Sir/Mam'
            item = row[3]
            code = row[5]
            area = row[2]
            for receiver_email in recipients:
                construct_email(receiver_email, receiver_name, item, code, area, status)

send_reminders()

# schedule.every().day.at("15:32").do(send_reminders)
# while True:
#     schedule.run_pending()
#     time.sleep(1)

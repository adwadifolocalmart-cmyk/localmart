<?php
session_start();
$page_title = "About Us | Adwadifo";
include 'header.php';
?>

<style>
    :root {
        --primary-color: #2c6b2f;
        --secondary-color: #4caf50;
    }

    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        background: #f9f9f9;
        color: #333;
    }

    .about-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        text-align: center;
        padding: 40px 20px;
    }

    .about-header h1 {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }

    .about-header p {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    .about-section {
        max-width: 1000px;
        margin: 60px auto;
        padding: 0 20px;
    }

    .about-section h2 {
        color: var(--primary-color);
        margin-bottom: 15px;
        font-size: 1.8rem;
    }

    .about-section p {
        line-height: 1.7;
        margin-bottom: 25px;
        font-size: 1rem;
    }

    .mission-vision {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-top: 40px;
    }

    .mission-box, .vision-box {
        background: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .mission-box h3, .vision-box h3 {
        color: var(--primary-color);
        margin-bottom: 10px;
        font-size: 1.4rem;
    }

    .team-section {
        text-align: center;
        margin-top: 60px;
    }

    .team-section h2 {
        color: var(--primary-color);
        margin-bottom: 20px;
        font-size: 1.8rem;
    }

    .team-members {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 25px;
    }

    .team-member {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        width: 230px;
        text-align: center;
    }

    .team-member img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        border-radius: 8px;
    }

    .team-member h4 {
        margin-top: 10px;
        color: #333;
        font-size: 1.1rem;
    }

    .team-member p {
        font-size: 0.9rem;
        color: #666;
        margin: 4px 0;
    }

    @media (max-width: 768px) {
        .mission-vision {
            grid-template-columns: 1fr;
        }

        .about-header h1 {
            font-size: 2rem;
        }

        .about-section h2 {
            font-size: 1.5rem;
        }

        .team-member {
            width: 100%;
            max-width: 300px;
        }
    }
</style>

<section class="about-header">
    <h1>About Adwadifo</h1>
    <p>Connecting vendors and customers — one fresh product at a time.</p>
</section>

<section class="about-section">
    <h2>Who We Are</h2>
    <p>
        Adwadifo is a digital marketplace built to promote local food distribution and empower small-scale farmers and vendors. 
        Our platform bridges the gap between agricultural producers and consumers, ensuring that fresh produce, fruits, and other farm products 
        are easily accessible and delivered conveniently.
    </p>

    <h2>What We Do</h2>
    <p>
        We make local trading easy and digital. Vendors can register their farms or stores, upload available produce, 
        and reach a wider customer base, while customers enjoy real-time access to fresh food items without the hassle 
        of visiting physical markets. Adwadifo encourages fair trade, transparency, and community-based growth.
    </p>

    <div class="mission-vision">
        <div class="mission-box">
            <h3>Our Mission</h3>
            <p>
                To connect communities through technology by simplifying the way local food is bought and sold, 
                empowering vendors, and ensuring customers always get fresh, affordable, and quality produce.
            </p>
        </div>
        <div class="vision-box">
            <h3>Our Vision</h3>
            <p>
                To become Africa’s leading platform for sustainable local trade, improving food systems and empowering 
                millions of local vendors through digital transformation.
            </p>
        </div>
    </div>

    <div class="team-section">
        <h2>Meet Our Team</h2>
        <div class="team-members">
            <div class="team-member">
                <img src="uploads/Team/tonycryme.png" alt="Anthony Ekpe">
                <h4>Anthony Ekpe</h4>
                <p>Administrator</p>
                <p>0599891070</p>
            </div>
            <div class="team-member">
                <img src="uploads/Team/Ben1.jpeg" alt="Tsatsu Ebenezer Yaw">
                <h4>Tsatsu Ebenezer Yaw</h4>
                <p>CEO - Techvault Institute</p>
            </div>
            <div class="team-member">
                <img src="uploads/Team/Eddie2.jpg" alt="Edward H.P. Quarshie">
                <h4>Edward H.P. Quarshie</h4>
                <p>Product Designer - ED Consults</p>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

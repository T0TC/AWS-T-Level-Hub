"""
AI Chatbot Server for AWS T-Level Hub
Uses sentence-transformers for semantic understanding of user queries
instead of simple keyword matching.
"""

import json
import numpy as np
from flask import Flask, request, jsonify
from flask_cors import CORS
from sentence_transformers import SentenceTransformer, util
import atexit

app = Flask(__name__)
CORS(app)

# ===== KNOWLEDGE BASE (same content as before, but structured for embeddings) =====

knowledge_data = {
    "en": [
        # Courses
        {"question": "What is Digital Production T-Level?", "answer": "Digital Production, Design and Development T-Level covers software development, data analytics, and digital design. You'll learn programming, project management, and emerging technologies."},
        {"question": "What is Digital Support Services T-Level?", "answer": "Digital Support Services T-Level focuses on IT infrastructure, networking, cyber security, and cloud computing — ideal for roles like IT technician or network engineer."},
        {"question": "What is Digital Business Services T-Level?", "answer": "Digital Business Services T-Level covers data management, business analysis, and digital transformation — great for careers in data analytics and business intelligence."},
        {"question": "What is Education and Childcare T-Level?", "answer": "Education and Childcare T-Level prepares you for roles in early years education, teaching assistance, and childcare management with both classroom learning and practical placement."},
        {"question": "What is Health T-Level?", "answer": "Health T-Level covers core healthcare concepts, anatomy, and patient care. It's designed for those pursuing careers in nursing, paramedicine, or healthcare support."},
        {"question": "What is Healthcare Science T-Level?", "answer": "Healthcare Science T-Level focuses on laboratory sciences, medical physics, and physiological sciences — ideal for lab technician and clinical scientist roles."},
        {"question": "What is Building Services Engineering T-Level?", "answer": "Building Services Engineering T-Level covers heating, ventilation, air conditioning, lighting, and energy systems — preparing you for careers in building services and sustainable construction."},
        {"question": "What is Design Surveying and Planning T-Level?", "answer": "Design, Surveying and Planning T-Level teaches civil engineering, surveying, building design, and planning — great for construction and property careers."},
        {"question": "What is Accounting T-Level?", "answer": "Accounting T-Level provides training in financial accounting, management accounting, taxation, and bookkeeping — preparing you for careers in finance and accountancy."},
        {"question": "What is Finance T-Level?", "answer": "Finance T-Level covers financial services, investment management, insurance, and risk — opening doors to careers in banking, financial planning, and insurance."},
        
        # Placements
        {"question": "Tell me about AWS Cloud Support Placement", "answer": "The AWS Cloud Support Placement is based in London. You'll gain hands-on experience supporting cloud infrastructure and AWS services. It typically lasts 45 days (minimum) and you'll be mentored by AWS professionals."},
        {"question": "Tell me about Junior Software Developer placement", "answer": "The Junior Software Developer placement in Manchester lets you work with real development teams building applications. You'll learn agile methodologies, coding standards, and version control."},
        {"question": "Tell me about Cyber Security Assistant placement", "answer": "The Cyber Security Assistant placement in Birmingham focuses on security monitoring, threat detection, and vulnerability assessment. You'll work alongside experienced security analysts."},
        {"question": "Tell me about IT Helpdesk Technician placement", "answer": "The IT Helpdesk Technician placement in Leeds provides experience supporting users and maintaining IT systems. You'll develop troubleshooting and customer service skills."},
        
        # Careers
        {"question": "How much does a Software Developer earn?", "answer": "Software Developers earn between £30,000 and £60,000 per year on average. The relevant T-Level is Digital Production, Design and Development."},
        {"question": "How much does a Cloud Engineer earn?", "answer": "Cloud Engineers earn between £35,000 and £70,000 per year. The relevant T-Level is Digital Support Services."},
        {"question": "How much does a Cyber Security Analyst earn?", "answer": "Cyber Security Analysts earn between £35,000 and £65,000 per year. The relevant T-Level is Digital Business Services."},
        {"question": "How much does a Teaching Assistant earn?", "answer": "Teaching Assistants earn between £20,000 and £30,000 per year. The relevant T-Level is Education and Childcare."},
        {"question": "How much does a Healthcare Assistant earn?", "answer": "Healthcare Assistants earn between £22,000 and £35,000 per year. The relevant T-Level is Health."},
        {"question": "How much does a Laboratory Technician earn?", "answer": "Laboratory Technicians earn between £25,000 and £40,000 per year. The relevant T-Level is Healthcare Science."},
        
        # General
        {"question": "What are T-Levels?", "answer": "T-Levels are 2-year qualifications in England that combine classroom learning with a substantial industry placement (minimum 45 days / 315 hours). They are equivalent to 3 A-Levels and are co-created with employers like Amazon."},
        {"question": "How do I apply for a T-Level?", "answer": "To apply for a T-Level, you'll need to find a local college or school offering your chosen course. Entry requirements typically include 5 GCSEs at grade 4 or above, including English and Maths. You can apply through UCAS or directly to the college."},
        {"question": "Who is this portal for?", "answer": "This Amazon T-Level Hub is primarily designed for German students, teachers, and parents who want to learn about T-Levels as a route into UK industries. It provides information in both English and German."},
        {"question": "What is a T-Level qualification?", "answer": "A T-Level (Technical Level) is a nationally recognised qualification in England that focuses on vocational skills. Each T-Level includes a technical qualification, employer-set project, and compulsory industry placement."},
        {"question": "Are T-Levels suitable for German students?", "answer": "For German students, T-Levels offer a blend of practical and academic learning similar to the Abitur or vocational Ausbildung, but with a direct route into UK industries. The qualification is increasingly recognised by German employers too."},
        {"question": "Can international students study T-Levels?", "answer": "Yes, international students can study T-Levels in England. You'll need a suitable visa and English language proficiency. The placement component is a great way to gain UK work experience."},
        {"question": "How do T-Levels compare to A-Levels?", "answer": "T-Levels are equivalent to 3 A-Levels. While A-Levels are more academic and theory-based, T-Levels focus on practical skills with 80% classroom learning and 20% industry placement (minimum 45 days)."},
        {"question": "How long is a T-Level placement?", "answer": "The minimum industry placement is 45 days (approximately 315 hours). Some placements may be longer depending on the course provider and employer."},
        {"question": "Is there funding for T-Levels?", "answer": "T-Level funding is available through the UK government. For 16-19 year olds, the course is fully funded. There may be additional financial support available for travel, accommodation, and equipment."},
        {"question": "Can I go to university after a T-Level?", "answer": "Yes, T-Levels are accepted by many UK universities. The UCAS tariff points are equivalent to A-Levels. Some universities also value the industry placement experience highly in their admissions process."},
        {"question": "What is Amazon's role in T-Levels?", "answer": "Amazon is a key employer partner for T-Levels. We offer industry placements in cloud support (AWS), software development, and other digital roles. This portal helps you explore those opportunities."},
        {"question": "What salary can I expect after a T-Level?", "answer": "T-Level graduates can expect starting salaries between £20,000 and £35,000, with experienced professionals earning £60,000+ in fields like software development, cloud engineering, and cyber security."},
        {"question": "What can I do after completing a T-Level?", "answer": "After completing a T-Level, you can: (1) Enter skilled employment, (2) Start an apprenticeship or higher apprenticeship, (3) Study at university — T-Levels are UCAS-accepted, or (4) Do a higher technical qualification."},
        {"question": "What subjects are available for T-Levels?", "answer": "T-Levels are available in: Digital Production Design & Development, Digital Support Services, Digital Business Services, Education & Childcare, Health, Healthcare Science, Building Services Engineering, Design Surveying & Planning, Accounting, Finance, and more."},
        {"question": "Which employers support T-Levels?", "answer": "T-Levels are co-created by employers including Amazon, Fujitsu, EDF Energy, the NHS, and over 250 other leading employers — ensuring the skills you learn match real industry needs."},
        {"question": "Is there information for German students?", "answer": "For German students and families: T-Levels are recognised qualifications in England. They're increasingly valued by German employers with UK operations. The portal includes a German language toggle to help you navigate the information in Deutsch."}
    ],
    "de": [
        # Courses
        {"question": "Was ist der T-Level Digitale Produktion?", "answer": "Der T-Level Digitale Produktion, Design und Entwicklung umfasst Softwareentwicklung, Datenanalyse und digitales Design. Sie lernen Programmierung, Projektmanagement und neue Technologien."},
        {"question": "Was ist der T-Level Digitale Unterstützungsdienste?", "answer": "Der T-Level Digitale Unterstützungsdienste konzentriert sich auf IT-Infrastruktur, Netzwerke, Cybersicherheit und Cloud-Computing – ideal für Rollen wie IT-Techniker oder Netzwerkingenieur."},
        {"question": "Was ist der T-Level Digitale Geschäftsdienste?", "answer": "Der T-Level Digitale Geschäftsdienste umfasst Datenmanagement, Geschäftsanalyse und digitale Transformation – großartig für Karrieren in Datenanalyse und Business Intelligence."},
        {"question": "Was ist der T-Level Bildung und Kinderbetreuung?", "answer": "Der T-Level Bildung und Kinderbetreuung bereitet Sie auf Rollen in der frühkindlichen Bildung, als Lehrassistent und in der Kinderbetreuungsverwaltung vor, mit sowohl Unterricht als auch praktischem Praktikum."},
        {"question": "Was ist der T-Level Gesundheit?", "answer": "Der T-Level Gesundheit umfasst grundlegende Gesundheitskonzepte, Anatomie und Patientenversorgung. Er ist für diejenigen konzipiert, die eine Karriere in der Krankenpflege, Notfallmedizin oder Gesundheitsunterstützung anstreben."},
        {"question": "Was ist der T-Level Medizinwissenschaft?", "answer": "Der T-Level Medizinwissenschaft konzentriert sich auf Laborwissenschaften, medizinische Physik und physiologische Wissenschaften – ideal für Labortechniker und klinische Wissenschaftler."},
        {"question": "Was ist der T-Level Gebäudetechnik?", "answer": "Der T-Level Gebäudetechnik umfasst Heizung, Lüftung, Klimaanlage, Beleuchtung und Energiesysteme – Vorbereitung auf Karrieren in der Gebäudetechnik und nachhaltigem Bauen."},
        {"question": "Was ist der T-Level Design Vermessung und Planung?", "answer": "Der T-Level Design, Vermessung und Planung lehrt Tiefbau, Vermessung, Gebäudedesign und Planung – großartig für Karrieren im Bau- und Immobilienwesen."},
        {"question": "Was ist der T-Level Buchhaltung?", "answer": "Der T-Level Buchhaltung bietet Ausbildung in Finanzbuchhaltung, Kostenrechnung, Steuerwesen und Buchführung – Vorbereitung auf Karrieren in Finanzen und Buchhaltung."},
        {"question": "Was ist der T-Level Finanzen?", "answer": "Der T-Level Finanzen umfasst Finanzdienstleistungen, Investmentmanagement, Versicherungen und Risikomanagement – öffnet Türen zu Karrieren im Bankwesen, in der Finanzplanung und Versicherung."},
        
        # Placements
        {"question": "Erzählen Sie mir vom AWS Cloud-Support-Praktikum", "answer": "Das AWS Cloud-Support-Praktikum findet in London statt. Sie sammeln praktische Erfahrungen bei der Unterstützung von Cloud-Infrastruktur und AWS-Diensten. Es dauert in der Regel mindestens 45 Tage und Sie werden von AWS-Profis betreut."},
        {"question": "Erzählen Sie mir vom Junior-Softwareentwickler-Praktikum", "answer": "Das Junior-Softwareentwickler-Praktikum in Manchester ermöglicht Ihnen die Zusammenarbeit mit echten Entwicklungsteams bei der Erstellung von Anwendungen. Sie lernen agile Methoden, Codierungsstandards und Versionskontrolle."},
        {"question": "Erzählen Sie mir vom Cybersicherheits-Praktikum", "answer": "Das Cybersicherheits-Assistent-Praktikum in Birmingham konzentriert sich auf Sicherheitsüberwachung, Bedrohungserkennung und Schwachstellenbewertung. Sie arbeiten mit erfahrenen Sicherheitsanalysten zusammen."},
        {"question": "Erzählen Sie mir vom IT-Helpdesk-Praktikum", "answer": "Das IT-Helpdesk-Techniker-Praktikum in Leeds bietet Erfahrung bei der Unterstützung von Benutzern und der Wartung von IT-Systemen. Sie entwickeln Problemlösungs- und Kundendienstfähigkeiten."},
        
        # Careers
        {"question": "Wie viel verdient ein Softwareentwickler?", "answer": "Softwareentwickler verdienen durchschnittlich zwischen £30.000 und £60.000 pro Jahr. Der relevante T-Level ist Digitale Produktion, Design und Entwicklung."},
        {"question": "Wie viel verdient ein Cloud-Ingenieur?", "answer": "Cloud-Ingenieure verdienen durchschnittlich zwischen £35.000 und £70.000 pro Jahr. Der relevante T-Level ist Digitale Unterstützungsdienste."},
        {"question": "Wie viel verdient ein Cybersicherheitsanalyst?", "answer": "Cybersicherheitsanalysten verdienen durchschnittlich zwischen £35.000 und £65.000 pro Jahr. Der relevante T-Level ist Digitale Geschäftsdienste."},
        {"question": "Wie viel verdient ein Lehrassistent?", "answer": "Lehrassistenten verdienen durchschnittlich zwischen £20.000 und £30.000 pro Jahr. Der relevante T-Level ist Bildung und Kinderbetreuung."},
        {"question": "Wie viel verdient ein Gesundheitsassistent?", "answer": "Gesundheitsassistenten verdienen durchschnittlich zwischen £22.000 und £35.000 pro Jahr. Der relevante T-Level ist Gesundheit."},
        {"question": "Wie viel verdient ein Labortechniker?", "answer": "Labortechniker verdienen durchschnittlich zwischen £25.000 und £40.000 pro Jahr. Der relevante T-Level ist Medizinwissenschaft."},
        
        # General
        {"question": "Was sind T-Levels?", "answer": "T-Levels sind 2-jährige Qualifikationen in England, die Unterricht im Klassenzimmer mit einem umfangreichen Industriepraktikum (mindestens 45 Tage / 315 Stunden) kombinieren. Sie entsprechen 3 A-Levels und werden gemeinsam mit Arbeitgebern wie Amazon entwickelt."},
        {"question": "Wie bewerbe ich mich für einen T-Level?", "answer": "Um sich für einen T-Level zu bewerben, müssen Sie ein örtliches College oder eine Schule finden, die Ihren gewählten Kurs anbietet. Die Zulassungsvoraussetzungen umfassen in der Regel 5 GCSEs der Note 4 oder höher, einschließlich Englisch und Mathematik. Sie können sich über UCAS oder direkt bei der Hochschule bewerben."},
        {"question": "Für wen ist dieses Portal?", "answer": "Dieses Amazon T-Level-Hub ist hauptsächlich für deutsche Schüler, Lehrer und Eltern konzipiert, die mehr über T-Levels als Weg in britische Industrien erfahren möchten. Es bietet Informationen sowohl auf Englisch als auch auf Deutsch."},
        {"question": "Was ist eine T-Level-Qualifikation?", "answer": "Ein T-Level (Technical Level) ist eine national anerkannte Qualifikation in England, die sich auf berufliche Fähigkeiten konzentriert. Jeder T-Level umfasst eine technische Qualifikation, ein vom Arbeitgeber festgelegtes Projekt und ein obligatorisches Industriepraktikum."},
        {"question": "Sind T-Levels für deutsche Schüler geeignet?", "answer": "Für deutsche Schüler bieten T-Levels eine Mischung aus praktischem und akademischem Lernen, ähnlich wie das Abitur oder die berufliche Ausbildung, jedoch mit einem direkten Weg in britische Industrien. Die Qualifikation wird zunehmend auch von deutschen Arbeitgebern anerkannt."},
        {"question": "Können internationale Schüler T-Levels studieren?", "answer": "Ja, internationale Schüler können T-Levels in England studieren. Sie benötigen ein geeignetes Visum und Englischkenntnisse. Die Praktikumskomponente ist eine großartige Möglichkeit, britische Berufserfahrung zu sammeln."},
        {"question": "Wie vergleichen sich T-Levels mit A-Levels?", "answer": "T-Levels entsprechen 3 A-Levels. Während A-Levels akademischer und theorielastiger sind, konzentrieren sich T-Levels auf praktische Fähigkeiten mit 80% Unterricht und 20% Industriepraktikum (mindestens 45 Tage)."},
        {"question": "Wie lange dauert ein T-Level-Praktikum?", "answer": "Das Mindestindustriepraktikum beträgt 45 Tage (ca. 315 Stunden). Einige Praktika können je nach Kursanbieter und Arbeitgeber länger dauern."},
        {"question": "Gibt es Finanzierung für T-Levels?", "answer": "Die T-Level-Finanzierung erfolgt durch die britische Regierung. Für 16- bis 19-Jährige ist der Kurs vollständig finanziert. Es können zusätzliche finanzielle Unterstützungen für Reise, Unterkunft und Ausrüstung verfügbar sein."},
        {"question": "Kann ich nach einem T-Level zur Universität gehen?", "answer": "Ja, T-Levels werden von vielen britischen Universitäten anerkannt. Die UCAS-Tarifpunkte entsprechen denen von A-Levels. Einige Universitäten schätzen die Industriepraktikumserfahrung im Zulassungsverfahren sehr."},
        {"question": "Welche Rolle spielt Amazon bei T-Levels?", "answer": "Amazon ist ein wichtiger Arbeitgeberpartner für T-Levels. Wir bieten Industriepraktika in den Bereichen Cloud-Support (AWS), Softwareentwicklung und anderen digitalen Rollen an. Dieses Portal hilft Ihnen, diese Möglichkeiten zu erkunden."},
        {"question": "Welches Gehalt kann ich nach einem T-Level erwarten?", "answer": "T-Level-Absolventen können mit Einstiegsgehältern zwischen £20.000 und £35.000 rechnen, während erfahrene Fachkräfte in Bereichen wie Softwareentwicklung, Cloud-Engineering und Cybersicherheit £60.000+ verdienen."},
        {"question": "Was kann ich nach Abschluss eines T-Levels tun?", "answer": "Nach Abschluss eines T-Levels können Sie: (1) Eine qualifizierte Beschäftigung aufnehmen, (2) Eine Ausbildung oder höhere Ausbildung beginnen, (3) An einer Universität studieren – T-Levels werden von UCAS anerkannt, oder (4) Eine höhere technische Qualifikation erwerben."},
        {"question": "Welche Fächer sind bei T-Levels verfügbar?", "answer": "T-Levels sind verfügbar in: Digitale Produktion Design & Entwicklung, Digitale Unterstützungsdienste, Digitale Geschäftsdienste, Bildung & Kinderbetreuung, Gesundheit, Medizinwissenschaft, Gebäudetechnik, Design Vermessung & Planung, Buchhaltung, Finanzen und mehr."},
        {"question": "Welche Arbeitgeber unterstützen T-Levels?", "answer": "T-Levels werden gemeinsam von Arbeitgebern wie Amazon, Fujitsu, EDF Energy, dem NHS und über 250 anderen führenden Arbeitgebern entwickelt – so wird sichergestellt, dass die erlernten Fähigkeiten den tatsächlichen Anforderungen der Industrie entsprechen."},
        {"question": "Gibt es Informationen für deutsche Schüler?", "answer": "Für deutsche Schüler und Familien: T-Levels sind anerkannte Qualifikationen in England. Sie werden zunehmend von deutschen Arbeitgebern mit Niederlassungen in Großbritannien geschätzt. Das Portal enthält einen deutschen Sprachumschalter, damit Sie die Informationen auf Deutsch lesen können."}
    ]
}

# Pre-computed fallback responses
fallback_responses = {
    "en": {
        "greeting": "Hello! Welcome to the Amazon T-Level Hub. I can answer questions about T-Level courses, industry placements, career opportunities, applications, and more. How can I help you today?",
        "thanks": "You're welcome! If you have any more questions about T-Levels, feel free to ask.",
        "goodbye": "Goodbye! Feel free to return anytime if you have more questions about T-Levels.",
        "help": "I can answer questions about: T-Level courses (Digital, Health, Construction, etc.), industry placements (AWS Cloud, Software Dev, Cyber Security), career pathways and salaries, the application process, how T-Levels compare to A-Levels, funding, university progression, and international student information. Just ask!",
        "unknown": "I'm not sure I have a specific answer for that. Try asking about: T-Level courses (like Digital Production or Health), industry placements, career salaries, how to apply, or what T-Levels are. I'm here to help!"
    },
    "de": {
        "greeting": "Hallo! Willkommen im Amazon T-Level-Hub. Ich kann Fragen zu T-Level-Kursen, Industriepraktika, Karrieremöglichkeiten, Bewerbungen und mehr beantworten. Wie kann ich Ihnen heute helfen?",
        "thanks": "Gern geschehen! Wenn Sie weitere Fragen zu T-Levels haben, fragen Sie einfach.",
        "goodbye": "Auf Wiedersehen! Kommen Sie jederzeit zurück, wenn Sie weitere Fragen zu T-Levels haben.",
        "help": "Ich kann Fragen beantworten zu: T-Level-Kursen (Digital, Gesundheit, Bauwesen usw.), Industriepraktika (AWS Cloud, Softwareentwicklung, Cybersicherheit), Karrierewegen und Gehältern, dem Bewerbungsprozess, wie T-Levels im Vergleich zu A-Levels abschneiden, Finanzierung, Universitätszugang und Informationen für internationale Schüler. Fragen Sie einfach!",
        "unknown": "Ich bin mir nicht sicher, ob ich eine spezifische Antwort darauf habe. Versuchen Sie zu fragen nach: T-Level-Kursen (wie Digitale Produktion oder Gesundheit), Industriepraktika, Karrieregehältern, wie man sich bewirbt oder was T-Levels sind. Ich bin hier, um zu helfen!"
    }
}


# Global model variable - loaded once at startup
model = None
# Pre-computed embeddings
embeddings = {}
questions_list = {}


def load_model():
    """Load the sentence transformer model (called once at startup)."""
    global model
    print("Loading AI model (all-MiniLM-L6-v2)...")
    model = SentenceTransformer('all-MiniLM-L6-v2')
    print("Model loaded successfully!")


def compute_embeddings():
    """Compute embeddings for all knowledge base questions."""
    global embeddings, questions_list
    for lang in ["en", "de"]:
        items = knowledge_data[lang]
        questions = [item["question"] for item in items]
        questions_list[lang] = questions
        emb = model.encode(questions, convert_to_tensor=True)
        embeddings[lang] = emb
    print("Embeddings computed for all knowledge base entries.")


def find_best_answer(user_query, lang="en", threshold=0.3):
    """
    Find the best answer using semantic similarity.
    Uses cosine similarity between the query embedding and all question embeddings.
    """
    # Encode the user query
    query_emb = model.encode(user_query, convert_to_tensor=True)
    
    # Compute cosine similarities with all questions in the target language
    lang_embeddings = embeddings[lang]
    cos_scores = util.cos_sim(query_emb, lang_embeddings)[0]
    
    # Find the best match
    best_idx = int(np.argmax(cos_scores))
    best_score = float(cos_scores[best_idx])
    
    # If similarity is high enough, return the corresponding answer
    if best_score >= threshold:
        return knowledge_data[lang][best_idx]["answer"]
    
    # Fallback to intent detection using direct patterns
    q = user_query.lower().strip()
    fallbacks = fallback_responses[lang]
    
    # Greeting detection
    greeting_patterns_en = ["^hi", "^hello", "^hey", "^good ", "^morning", "^afternoon", "^evening", "how are you"]
    greeting_patterns_de = ["^hallo", "^hi", "^hey", "^guten ", "^morgen", "^tag", "^grüß", "wie geht"]
    
    # Thanks detection
    thanks_patterns = ["thank", "thanks", "cheers", "danke", "vielen", "dank"]
    
    # Goodbye detection
    goodbye_patterns_en = ["bye", "goodbye", "see you", "farewell"]
    goodbye_patterns_de = ["tschüss", "auf wiedersehen", "bye"]
    
    # Help detection
    help_patterns_en = ["what can you", "help", "capabilities", "what do you"]
    help_patterns_de = ["was kannst du", "hilfe", "funktionen"]
    
    if lang == "de":
        import re
        for pat in greeting_patterns_de:
            if re.search(pat, q):
                return fallbacks["greeting"]
        for pat in thanks_patterns:
            if re.search(pat, q):
                return fallbacks["thanks"]
        for pat in goodbye_patterns_de:
            if re.search(pat, q):
                return fallbacks["goodbye"]
        for pat in help_patterns_de:
            if re.search(pat, q):
                return fallbacks["help"]
        # If German detected but user wrote in English, respond in German
        if any(re.search(p, q) for p in greeting_patterns_en):
            return fallbacks["greeting"]
    else:
        import re
        for pat in greeting_patterns_en:
            if re.search(pat, q):
                return fallbacks["greeting"]
        for pat in thanks_patterns:
            if re.search(pat, q):
                return fallbacks["thanks"]
        for pat in goodbye_patterns_en:
            if re.search(pat, q):
                return fallbacks["goodbye"]
        for pat in help_patterns_en:
            if re.search(pat, q):
                return fallbacks["help"]
        # If user wrote in German patterns, respond in English with switch suggestion
        if any(re.search(p, q) for p in greeting_patterns_de):
            return "Hallo! You seem to be writing in German. You can switch the language using the Settings menu (top right) or continue in English. How can I help you with T-Levels?"
    
    # Try similarity in the other language as fallback
    other_lang = "de" if lang == "en" else "en"
    other_embeddings = embeddings[other_lang]
    other_scores = util.cos_sim(query_emb, other_embeddings)[0]
    other_best_idx = int(np.argmax(other_scores))
    other_best_score = float(other_scores[other_best_idx])
    
    if other_best_score >= threshold + 0.1:
        other_answer = knowledge_data[other_lang][other_best_idx]["answer"]
        if lang == "en":
            return f"I found information in German that may help: {other_answer}\n\n(You can switch to German in Settings for more answers in Deutsch.)"
        else:
            return f"Ich habe Informationen auf Englisch gefunden, die helfen könnten: {other_answer}\n\n(Sie können in den Einstellungen auf Englisch umschalten.)"
    
    return fallbacks["unknown"]


# ===== Flask API Routes =====

@app.route("/api/chat", methods=["POST"])
def chat():
    data = request.get_json()
    if not data or "message" not in data:
        return jsonify({"error": "No message provided"}), 400
    
    user_message = data["message"]
    lang = data.get("language", "en")
    
    answer = find_best_answer(user_message, lang)
    
    return jsonify({
        "response": answer,
        "language": lang
    })


@app.route("/api/health", methods=["GET"])
def health():
    return jsonify({"status": "ok", "model_loaded": model is not None})


# Initialize the model at startup
def init_model():
    load_model()
    compute_embeddings()

init_model()

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=False)
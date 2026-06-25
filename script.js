// Home button scrolls to top

document
.getElementById("homeBtn")
.addEventListener("click", function(e){

    e.preventDefault();

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });

});


// Scroll to section when card button clicked

function scrollToSection(id){

    document
    .getElementById(id)
    .scrollIntoView({
        behavior:"smooth"
    });

}

// --- Settings modal open/close ---
const settingsBtn = document.getElementById("settingsBtn");
const settingsOverlay = document.getElementById("settingsOverlay");
const closeSettings = document.getElementById("closeSettings");

settingsBtn.addEventListener("click", function (e) {
    e.preventDefault();
    settingsOverlay.classList.add("active");
});

closeSettings.addEventListener("click", function () {
    settingsOverlay.classList.remove("active");
});

settingsOverlay.addEventListener("click", function (e) {
    if (e.target === settingsOverlay) {
        settingsOverlay.classList.remove("active");
    }
});

// --- Font size control ---
const fontIncrease = document.getElementById("fontIncrease");
const fontDecrease = document.getElementById("fontDecrease");
const fontReset = document.getElementById("fontReset");

let currentFontSize = parseInt(localStorage.getItem("fontSize")) || 100;

function applyFontSize() {
    const scale = currentFontSize / 100;
    document.body.style.zoom = scale;
    localStorage.setItem("fontSize", currentFontSize);
}

fontIncrease.addEventListener("click", function () {
    if (currentFontSize < 150) {
        currentFontSize += 10;
        applyFontSize();
    }
});

fontDecrease.addEventListener("click", function () {
    if (currentFontSize > 70) {
        currentFontSize -= 10;
        applyFontSize();
    }
});

fontReset.addEventListener("click", function () {
    currentFontSize = 100;
    applyFontSize();
});

applyFontSize();

// --- Accessibility toggles ---
const highContrastToggle = document.getElementById("highContrastToggle");
const underlineLinksToggle = document.getElementById("underlineLinksToggle");
const reduceMotionToggle = document.getElementById("reduceMotionToggle");

function setBodyClass(className, isOn) {
    document.body.classList.toggle(className, isOn);
    localStorage.setItem(className, isOn);
}

highContrastToggle.addEventListener("change", function () {
    setBodyClass("high-contrast", this.checked);
});

underlineLinksToggle.addEventListener("change", function () {
    setBodyClass("underline-links", this.checked);
});

reduceMotionToggle.addEventListener("change", function () {
    setBodyClass("reduce-motion", this.checked);
});

reduceMotionToggle.addEventListener("change", function () {
    setBodyClass("reduce-motion", this.checked);
});

const lightThemeToggle = document.getElementById("lightThemeToggle");

if (lightThemeToggle) {
    lightThemeToggle.checked = localStorage.getItem("light-theme") === "true";
    setBodyClass("light-theme", lightThemeToggle.checked);
    lightThemeToggle.addEventListener("change", function () {
        setBodyClass("light-theme", this.checked);
    });
}

// Restore accessibility settings on page load
window.addEventListener("DOMContentLoaded", function () {
    const hc = localStorage.getItem("high-contrast") === "true";
    const ul = localStorage.getItem("underline-links") === "true";
    const rm = localStorage.getItem("reduce-motion") === "true";
    const lt = localStorage.getItem("light-theme") === "true";

    highContrastToggle.checked = hc;
    underlineLinksToggle.checked = ul;
    reduceMotionToggle.checked = rm;
    if (lightThemeToggle) lightThemeToggle.checked = lt;

    setBodyClass("high-contrast", hc);
    setBodyClass("underline-links", ul);
    setBodyClass("reduce-motion", rm);
    setBodyClass("light-theme", lt);
});

// --- Language switching ---
const languageSelect = document.getElementById("languageSelect");
const footerLangSelect = document.getElementById("footerLangSelect");

const translations = {
   en: {

        // Nav
        home: "Home",
        apply: "Application",
        resources: "Resources",
        chatbot: "Chatbot",
        settings: "Settings",
        contacts: "Contacts",

        // Category bar
        students: "Students",
        parents: "Parents & Guardians",
        teachers: "Teachers & Schools",
        accessibility: "Accessibility",
        administration: "Administration",

        // Card headings
        tlevelCourses: "T-Level Courses",
        industryPlacements: "Industry Placements",
        careerOpportunities: "Career Opportunities",

        // Card descriptions
        cardDescCourses: "Explore available T-Level qualifications, course content and progression pathways.",
        cardDescPlacements: "Discover current placement opportunities and gain valuable workplace experience.",
        cardDescCareers: "Learn about careers available after completing your chosen T-Level.",

        // Card buttons
        learnMore: "Learn More",

        // Course search
        searchPlaceholder: "Search T-Level courses...",

        // Course items
        courseDigitalProd: "Digital Production, Design and Development",
        courseDigitalSupport: "Digital Support Services",
        courseDigitalBusiness: "Digital Business Services",
        courseEducation: "Education and Childcare",
        courseHealth: "Health",
        courseHealthcareScience: "Healthcare Science",
        courseBuildingServices: "Building Services Engineering",
        courseDesignSurveying: "Design, Surveying and Planning",
        courseAccounting: "Accounting",
        courseFinance: "Finance",

        // Placement cards
        placementCloudTitle: "AWS Cloud Support Placement",
        placementCloudDesc: "Gain experience supporting cloud infrastructure and AWS services.",
        placementCloudLocation: "Location: London",
        placementDevTitle: "Junior Software Developer",
        placementDevDesc: "Work with development teams building real-world applications.",
        placementDevLocation: "Location: Manchester",
        placementCyberTitle: "Cyber Security Assistant",
        placementCyberDesc: "Learn about security monitoring and threat detection.",
        placementCyberLocation: "Location: Birmingham",
        placementHelpdeskTitle: "IT Helpdesk Technician",
        placementHelpdeskDesc: "Support users and maintain IT systems.",
        placementHelpdeskLocation: "Location: Leeds",

        // Career cards
        careerSoftwareTitle: "Software Developer",
        careerSoftwareSalary: "Average Salary: £30,000 - £60,000",
        careerSoftwareTlevel: "Relevant T-Level: Digital Production, Design & Development",
        careerCloudTitle: "Cloud Engineer",
        careerCloudSalary: "Average Salary: £35,000 - £70,000",
        careerCloudTlevel: "Relevant T-Level: Digital Support Services",
        careerCyberTitle: "Cyber Security Analyst",
        careerCyberSalary: "Average Salary: £35,000 - £65,000",
        careerCyberTlevel: "Relevant T-Level: Digital Business Services",
        careerTeachingTitle: "Teaching Assistant",
        careerTeachingSalary: "Average Salary: £20,000 - £30,000",
        careerTeachingTlevel: "Relevant T-Level: Education & Childcare",
        careerHealthcareTitle: "Healthcare Assistant",
        careerHealthcareSalary: "Average Salary: £22,000 - £35,000",
        careerHealthcareTlevel: "Relevant T-Level: Health",
        careerLabTitle: "Laboratory Technician",
        careerLabSalary: "Average Salary: £25,000 - £40,000",
        careerLabTlevel: "Relevant T-Level: Healthcare Science",

        // Info section headings
        headingStudents: "Students",
        headingTeachers: "Teachers & Schools",
        headingParents: "Parents & Guardians",
        headingAccessibility: "Accessibility",
        headingAdministration: "Administration",

        // Info section paragraphs (include <u> tags for inline emphasis)
        textStudents: "T-Levels are a new kind of qualification from England that combine classroom learning with a <u>substantial industry placement</u>, giving students <u>real workplace experience</u> long before they graduate. For German students used to systems like the Abitur or vocational Ausbildung, T-Levels offer a similar blend of practical and academic learning, but with a <u>direct route into UK industries</u> such as digital technology, healthcare, construction, and engineering. This section helps students explore which T-Level pathway matches their interests, see what a typical placement looks like, and understand how the qualification could <u>open doors to study or work opportunities in the UK</u>.",
        textTeachers: "This section is designed for German schools and educators who may be advising students on <u>international study options</u> for the first time. It provides curriculum outlines, comparisons between T-Levels and familiar German qualifications, and practical guidance on how schools can support students interested in applying. Teachers will also find resources to help explain the <u>placement structure</u> to students and parents, along with information on how schools can establish a relationship as a <u>recognized partner institution</u>.",
        textParents: "Many German parents will not have encountered T-Levels before, so this section focuses on building trust and clarity around what the qualification involves, how it's recognized, and what kind of support structure exists around a placement abroad. It explains practical concerns such as <u>safeguarding</u>, <u>supervision during industry placements</u>, and how progress is communicated back to families, helping parents feel confident that their child is entering a <u>well-structured and supported program</u> rather than an unfamiliar gap-year alternative.",
        textAccessibility: "Since this portal serves an international audience navigating an unfamiliar education system, accessibility features are built in to remove language and usability barriers from the start. This includes a <u>German-language toggle</u> so families can read everything in their native language, <u>adjustable font sizing</u> for visual comfort, <u>high-contrast display options</u>, and screen-reader-friendly navigation. The goal is to make sure no student or parent is excluded from understanding their options simply because the system is new or the interface wasn't designed with them in mind.",
        textAdministration: "This section is intended for Amazon staff responsible for running and maintaining the T-Level portal rather than for students, parents, or schools. From here, administrators can <u>manage website content</u>, updating course listings, placement opportunities, and translated text as the program grows. Administrators can also <u>access submitted forms</u>, reviewing applications, placement enquiries, and contact requests as they come in from students, parents, and partner schools. Finally, administrators can <u>generate reports</u> summarizing engagement, placement uptake, and course interest, giving the team visibility into how the program is performing across Germany and where outreach efforts may need to be adjusted.",

        // Footer
        footerGetToKnow: "Get to Know T-Levels",
        footerAbout: "About T-Levels",
        footerWhyChoose: "Why Choose T-Levels",
        footerSuccess: "Success Stories",
        footerNews: "News & Updates",
        footerForSchools: "For Schools & Teachers",
        footerPartner: "Partner With Us",
        footerCurriculum: "Curriculum Resources",
        footerSchoolVisit: "Book a School Visit",
        footerToolkit: "Teacher Toolkit",
        footerPlacements: "Industry Placements",
        footerPlacementPartner: "Become a Placement Partner",
        footerGuidelines: "Placement Guidelines",
        footerEmployerStories: "Employer Success Stories",
        footerFunding: "Funding & Incentives",
        footerSupport: "Support",
        footerFAQs: "FAQs",
        footerAppGuide: "Application Guide",
        footerAdvisor: "Contact an Advisor",
        footerHelp: "Help Centre",
        footerPortal: "T-Level Portal",
        footerDigital: "Digital",
        footerDigitalDesc: "Software, data & infrastructure",
        footerConstruction: "Construction",
        footerConstructionDesc: "Design, surveying & building services",
        footerHealthScience: "Health & Science",
        footerHealthScienceDesc: "Healthcare & laboratory routes",
        footerEngineering: "Engineering",
        footerEngineeringDesc: "Manufacturing & control",
        footerBusiness: "Business",
        footerBusinessDesc: "Management & administration",
        footerEducationChildcare: "Education & Childcare",
        footerEducationChildcareDesc: "Early years & teaching support",
        footerLegalFinance: "Legal & Finance",
        footerLegalFinanceDesc: "Accounting & legal services",
        footerConditions: "Conditions of Use",
        footerPrivacy: "Privacy Notice",
        footerAccessibility: "Accessibility Statement",
        footerCopyright: "© 2026 T-Level Portal. For guidance only — not an official government resource.",

        // Settings modal
        settingsTitle: "Settings",
        settingsLanguage: "Language",
        settingsFontSize: "Font Size",
        settingsAccessibility: "Accessibility",
        settingsHighContrast: "High contrast mode",
        settingsUnderlineLinks: "Underline all links",
        settingsReduceMotion: "Reduce motion/animations",
        settingsFontReset: "Reset",

        // Contacts modal
        contactsTitle: "Contact Us",
        contactsEmail: "Email Address",
        contactsEmailPlaceholder: "Enter your email address",
        contactsSubject: "Subject",
        contactsSubjectPlaceholder: "What is your question about?",
        contactsMessage: "Message",
        contactsMessagePlaceholder: "Ask a question about T-Levels, industry placements, career pathways, applications, or support...",
        contactsSubmit: "Submit Enquiry",
        contactsAlertComplete: "Please complete all fields.",
        contactsAlertThanks: "Thank you for your enquiry. A member of the T-Level support team will contact you shortly.",

        // Chatbot
        chatbotTitle: "T-Level Assistant",
        chatbotInputPlaceholder: "Type your question...",
        chatbotWelcome: "Hello! I'm the T-Level assistant. Ask me anything about T-Level courses, industry placements, careers, applications, and more."

    },

    de: {

        // Nav
        home: "Startseite",
        apply: "Bewerbung",
        resources: "Ressourcen",
        chatbot: "Chatbot",
        settings: "Einstellungen",
        contacts: "Kontakt",

        // Category bar
        students: "Schüler",
        parents: "Eltern & Erziehungsberechtigte",
        teachers: "Lehrer & Schulen",
        accessibility: "Barrierefreiheit",
        administration: "Verwaltung",

        // Card headings
        tlevelCourses: "T-Level-Kurse",
        industryPlacements: "Praktikumsplätze",
        careerOpportunities: "Karrieremöglichkeiten",

        // Card descriptions
        cardDescCourses: "Entdecken Sie verfügbare T-Level-Qualifikationen, Kursinhalte und Fortschrittswege.",
        cardDescPlacements: "Entdecken Sie aktuelle Praktikumsmöglichkeiten und sammeln Sie wertvolle Berufserfahrung.",
        cardDescCareers: "Erfahren Sie mehr über Karrieremöglichkeiten nach Abschluss Ihres T-Levels.",

        // Card buttons
        learnMore: "Mehr Erfahren",

        // Course search
        searchPlaceholder: "T-Level-Kurse durchsuchen...",

        // Course items
        courseDigitalProd: "Digitale Produktion, Design und Entwicklung",
        courseDigitalSupport: "Digitale Unterstützungsdienste",
        courseDigitalBusiness: "Digitale Geschäftsdienste",
        courseEducation: "Bildung und Kinderbetreuung",
        courseHealth: "Gesundheit",
        courseHealthcareScience: "Medizinwissenschaft",
        courseBuildingServices: "Gebäudetechnik",
        courseDesignSurveying: "Design, Vermessung und Planung",
        courseAccounting: "Buchhaltung",
        courseFinance: "Finanzen",

        // Placement cards
        placementCloudTitle: "AWS Cloud-Support-Praktikum",
        placementCloudDesc: "Sammeln Sie Erfahrungen bei der Unterstützung von Cloud-Infrastruktur und AWS-Diensten.",
        placementCloudLocation: "Standort: London",
        placementDevTitle: "Junior Softwareentwickler",
        placementDevDesc: "Arbeiten Sie mit Entwicklungsteams an realen Anwendungen.",
        placementDevLocation: "Standort: Manchester",
        placementCyberTitle: "Cybersicherheits-Assistent",
        placementCyberDesc: "Lernen Sie Sicherheitsüberwachung und Bedrohungserkennung kennen.",
        placementCyberLocation: "Standort: Birmingham",
        placementHelpdeskTitle: "IT-Helpdesk-Techniker",
        placementHelpdeskDesc: "Unterstützen Sie Benutzer und warten Sie IT-Systeme.",
        placementHelpdeskLocation: "Standort: Leeds",

        // Career cards
        careerSoftwareTitle: "Softwareentwickler",
        careerSoftwareSalary: "Durchschnittsgehalt: £30.000 - £60.000",
        careerSoftwareTlevel: "Relevanter T-Level: Digitale Produktion, Design & Entwicklung",
        careerCloudTitle: "Cloud-Ingenieur",
        careerCloudSalary: "Durchschnittsgehalt: £35.000 - £70.000",
        careerCloudTlevel: "Relevanter T-Level: Digitale Unterstützungsdienste",
        careerCyberTitle: "Cybersicherheitsanalyst",
        careerCyberSalary: "Durchschnittsgehalt: £35.000 - £65.000",
        careerCyberTlevel: "Relevanter T-Level: Digitale Geschäftsdienste",
        careerTeachingTitle: "Lehrassistent",
        careerTeachingSalary: "Durchschnittsgehalt: £20.000 - £30.000",
        careerTeachingTlevel: "Relevanter T-Level: Bildung & Kinderbetreuung",
        careerHealthcareTitle: "Gesundheitsassistent",
        careerHealthcareSalary: "Durchschnittsgehalt: £22.000 - £35.000",
        careerHealthcareTlevel: "Relevanter T-Level: Gesundheit",
        careerLabTitle: "Labortechniker",
        careerLabSalary: "Durchschnittsgehalt: £25.000 - £40.000",
        careerLabTlevel: "Relevanter T-Level: Medizinwissenschaft",

        // Info section headings
        headingStudents: "Schüler",
        headingTeachers: "Lehrer & Schulen",
        headingParents: "Eltern & Erziehungsberechtigte",
        headingAccessibility: "Barrierefreiheit",
        headingAdministration: "Verwaltung",

        // Info section paragraphs
        textStudents: "T-Levels sind eine neue Art von Qualifikation aus England, die Unterricht im Klassenzimmer mit einem umfangreichen Industriepraktikum kombinieren und den Schülern echte Berufserfahrung lange vor ihrem Abschluss ermöglichen. Für deutsche Schüler, die Systeme wie das Abitur oder die berufliche Ausbildung gewohnt sind, bieten T-Levels eine ähnliche Mischung aus praktischem und akademischem Lernen, jedoch mit einem direkten Weg in britische Industrien wie Digitaltechnologie, Gesundheitswesen, Bauwesen und Ingenieurwesen. Dieser Abschnitt hilft Schülern zu erkunden, welcher T-Level-Pfad ihren Interessen entspricht, wie ein typisches Praktikum aussieht und wie die Qualifikation Türen zu Studien- oder Arbeitsmöglichkeiten in Großbritannien öffnen kann.",
        textTeachers: "Dieser Abschnitt richtet sich an deutsche Schulen und Pädagogen, die Schüler möglicherweise zum ersten Mal zu internationalen Studienmöglichkeiten beraten. Er bietet Lehrplanübersichten, Vergleiche zwischen T-Levels und vertrauten deutschen Qualifikationen sowie praktische Anleitungen, wie Schulen Schüler bei der Bewerbung unterstützen können. Lehrer finden auch Ressourcen, um die Praktikumsstruktur Schülern und Eltern zu erklären, sowie Informationen darüber, wie Schulen eine Beziehung als anerkannte Partnerinstitution aufbauen können.",
        textParents: "Viele deutsche Eltern sind bisher nicht mit T-Levels in Berührung gekommen, daher konzentriert sich dieser Abschnitt darauf, Vertrauen und Klarheit darüber zu schaffen, was die Qualifikation beinhaltet, wie sie anerkannt wird und welche Unterstützungsstruktur rund um ein Praktikum im Ausland existiert. Er erklärt praktische Anliegen wie Schutzmaßnahmen, Betreuung während Industriepraktiken und wie der Fortschritt an die Familien kommuniziert wird. Dies hilft Eltern, zuversichtlich zu sein, dass ihr Kind an einem gut strukturierten und unterstützten Programm teilnimmt und nicht an einer unbekannten Alternative zum Auslandsjahr.",
        textAccessibility: "Da dieses Portal ein internationales Publikum bedient, das sich in einem unbekannten Bildungssystem zurechtfindet, sind Barrierefreiheitsfunktionen von Anfang an integriert, um Sprach- und Benutzerfreundlichkeitsbarrieren zu beseitigen. Dazu gehört ein deutscher Sprachumschalter, damit Familien alles in ihrer Muttersprache lesen können, anpassbare Schriftgrößen für visuellen Komfort, kontrastreiche Anzeigeoptionen und eine bildschirmleserfreundliche Navigation. Das Ziel ist sicherzustellen, dass kein Schüler oder Elternteil vom Verständnis seiner Möglichkeiten ausgeschlossen wird, nur weil das System neu ist oder die Benutzeroberfläche nicht mit ihnen im Hinterkopf gestaltet wurde.",
        textAdministration: "Dieser Abschnitt ist für Amazon-Mitarbeiter gedacht, die für den Betrieb und die Wartung des T-Level-Portals verantwortlich sind, nicht für Schüler, Eltern oder Schulen. Von hier aus können Administratoren Website-Inhalte verwalten, Kurslisten, Praktikumsmöglichkeiten und übersetzte Texte aktualisieren, während das Programm wächst. Administratoren können auch eingereichte Formulare einsehen, Bewerbungen, Praktikumsanfragen und Kontaktanfragen von Schülern, Eltern und Partnerschulen prüfen. Schließlich können Administratoren Berichte erstellen, die Engagement, Praktikumsaufnahme und Kursinteresse zusammenfassen, um dem Team Einblicke zu geben, wie das Programm in Deutschland läuft und wo die Öffentlichkeitsarbeit möglicherweise angepasst werden muss.",

        // Footer
        footerGetToKnow: "T-Levels Kennenlernen",
        footerAbout: "Über T-Levels",
        footerWhyChoose: "Warum T-Levels Wählen",
        footerSuccess: "Erfolgsgeschichten",
        footerNews: "Neuigkeiten & Updates",
        footerForSchools: "Für Schulen & Lehrer",
        footerPartner: "Partner Werden",
        footerCurriculum: "Lehrplanressourcen",
        footerSchoolVisit: "Schulbesuch Buchen",
        footerToolkit: "Lehrer-Werkzeugkasten",
        footerPlacements: "Industriepraktika",
        footerPlacementPartner: "Praktikumspartner Werden",
        footerGuidelines: "Praktikumsrichtlinien",
        footerEmployerStories: "Erfolgsgeschichten von Arbeitgebern",
        footerFunding: "Finanzierung & Anreize",
        footerSupport: "Unterstützung",
        footerFAQs: "Häufig Gestellte Fragen",
        footerAppGuide: "Bewerbungsleitfaden",
        footerAdvisor: "Berater Kontaktieren",
        footerHelp: "Hilfecenter",
        footerPortal: "T-Level-Portal",
        footerDigital: "Digital",
        footerDigitalDesc: "Software, Daten & Infrastruktur",
        footerConstruction: "Bauwesen",
        footerConstructionDesc: "Design, Vermessung & Gebäudedienstleistungen",
        footerHealthScience: "Gesundheit & Wissenschaft",
        footerHealthScienceDesc: "Gesundheitswesen & Laborwege",
        footerEngineering: "Ingenieurwesen",
        footerEngineeringDesc: "Fertigung & Steuerung",
        footerBusiness: "Wirtschaft",
        footerBusinessDesc: "Management & Verwaltung",
        footerEducationChildcare: "Bildung & Kinderbetreuung",
        footerEducationChildcareDesc: "Frühe Jahre & Unterrichtsunterstützung",
        footerLegalFinance: "Recht & Finanzen",
        footerLegalFinanceDesc: "Buchhaltung & Rechtsdienstleistungen",
        footerConditions: "Nutzungsbedingungen",
        footerPrivacy: "Datenschutzerklärung",
        footerAccessibility: "Erklärung zur Barrierefreiheit",
        footerCopyright: "© 2026 T-Level-Portal. Nur zur Orientierung — keine offizielle Regierungsressource.",

        // Settings modal
        settingsTitle: "Einstellungen",
        settingsLanguage: "Sprache",
        settingsFontSize: "Schriftgröße",
        settingsAccessibility: "Barrierefreiheit",
        settingsHighContrast: "Kontrastmodus",
        settingsUnderlineLinks: "Alle Links unterstreichen",
        settingsReduceMotion: "Bewegung/Animationen reduzieren",
        settingsFontReset: "Zurücksetzen",

        // Contacts modal
        contactsTitle: "Kontakt",
        contactsEmail: "E-Mail-Adresse",
        contactsEmailPlaceholder: "Geben Sie Ihre E-Mail-Adresse ein",
        contactsSubject: "Betreff",
        contactsSubjectPlaceholder: "Worum geht es bei Ihrer Frage?",
        contactsMessage: "Nachricht",
        contactsMessagePlaceholder: "Stellen Sie eine Frage zu T-Levels, Praktikumsplätzen, Karrierewegen, Bewerbungen oder Unterstützung...",
        contactsSubmit: "Anfrage Senden",
        contactsAlertComplete: "Bitte füllen Sie alle Felder aus.",
        contactsAlertThanks: "Vielen Dank für Ihre Anfrage. Ein Mitglied des T-Level-Supportteams wird sich in Kürze bei Ihnen melden.",

        // Chatbot
        chatbotTitle: "T-Level-Assistent",
        chatbotInputPlaceholder: "Geben Sie Ihre Frage ein...",
        chatbotWelcome: "Hallo! Ich bin der T-Level-Assistent. Fragen Sie mich alles zu T-Level-Kursen, Industriepraktika, Karrieremöglichkeiten, Bewerbungen und mehr."

    }
};

function applyLanguage(lang) {
    // Update text content for elements with data-i18n
    document.querySelectorAll("[data-i18n]").forEach(function (el) {
        const key = el.getAttribute("data-i18n");
        if (translations[lang] && translations[lang][key]) {
            el.textContent = translations[lang][key];
        }
    });

    // Update placeholders for elements with data-i18n-placeholder
    document.querySelectorAll("[data-i18n-placeholder]").forEach(function (el) {
        const key = el.getAttribute("data-i18n-placeholder");
        if (translations[lang] && translations[lang][key]) {
            el.placeholder = translations[lang][key];
        }
    });

    // Update HTML content for elements with data-i18n-html
    document.querySelectorAll("[data-i18n-html]").forEach(function (el) {
        const key = el.getAttribute("data-i18n-html");
        if (translations[lang] && translations[lang][key]) {
            el.innerHTML = translations[lang][key];
        }
    });

    // Sync both language selectors
    if (languageSelect) languageSelect.value = lang;
    if (footerLangSelect) footerLangSelect.value = lang;

    localStorage.setItem("language", lang);
}

languageSelect.addEventListener("change", function () {
    applyLanguage(this.value);
});

// Sync footer language selector with settings
if (footerLangSelect) {
    footerLangSelect.addEventListener("change", function () {
        applyLanguage(this.value);
    });
}

window.addEventListener("DOMContentLoaded", function () {
    const savedLang = localStorage.getItem("language") || "en";
    applyLanguage(savedLang);
});

const courseSearch =
document.getElementById("courseSearch");

courseSearch.addEventListener("keyup", function(){

    let filter =
    courseSearch.value.toLowerCase();

    let courses =
    document.querySelectorAll(".course-item");

    courses.forEach(function(course){

        let text =
        course.textContent.toLowerCase();

        if(text.includes(filter)){
            course.style.display = "block";
        } else {
            course.style.display = "none";
        }

    });

});

function scrollToSection(id){

    document
        .getElementById(id)
        .scrollIntoView({
            behavior: "smooth"
        });

}

const contactsBtn =
document.getElementById("contactsBtn");

const contactsOverlay =
document.getElementById("contactsOverlay");

const closeContacts =
document.getElementById("closeContacts");

contactsBtn.addEventListener("click", function(e){

    e.preventDefault();

    contactsOverlay.style.display = "flex";

});

closeContacts.addEventListener("click", function(){

    contactsOverlay.style.display = "none";

});

document
.getElementById("sendContactMessage")
.addEventListener("click", function(){

    const email =
    document.getElementById("contactEmail").value;

    const subject =
    document.getElementById("contactSubject").value;

    const message =
    document.getElementById("contactMessage").value;

    if(!email || !subject || !message){

        const lang = localStorage.getItem("language") || "en";
        alert(translations[lang].contactsAlertComplete);

        return;
    }

    const lang = localStorage.getItem("language") || "en";
    alert(translations[lang].contactsAlertThanks);

    contactsOverlay.style.display = "none";

});

// ===== CHATBOT =====
// Fully self-contained chatbot with comprehensive T-Level knowledge base
// No external server required — works offline immediately.

// --- Comprehensive T-Level knowledge base ---
const chatbotKnowledge = {
    en: [
        // == Courses ==
        { keywords: ["digital production", "digital production design and development", "software development t-level"], answer: "Digital Production, Design and Development T-Level covers software development, data analytics, and digital design. You'll learn programming (Python, JavaScript), project management, and emerging technologies like AI and cloud computing." },
        { keywords: ["digital support", "digital support services", "it infrastructure t-level"], answer: "Digital Support Services T-Level focuses on IT infrastructure, networking, cyber security, and cloud computing — ideal for roles like IT technician, network engineer, or cyber security analyst." },
        { keywords: ["digital business", "digital business services", "data management t-level"], answer: "Digital Business Services T-Level covers data management, business analysis, and digital transformation — great for careers in data analytics and business intelligence." },
        { keywords: ["education and childcare", "education t-level", "childcare t-level", "teaching t-level"], answer: "Education and Childcare T-Level prepares you for roles in early years education, teaching assistance, and childcare management with both classroom learning and practical placement." },
        { keywords: ["health t-level", "healthcare t-level", "nursing t-level"], answer: "Health T-Level covers core healthcare concepts, anatomy, and patient care. It's designed for those pursuing careers in nursing, paramedicine, or healthcare support." },
        { keywords: ["healthcare science", "healthcare science t-level", "lab technician t-level"], answer: "Healthcare Science T-Level focuses on laboratory sciences, medical physics, and physiological sciences — ideal for lab technician and clinical scientist roles." },
        { keywords: ["building services", "building services engineering", "construction t-level"], answer: "Building Services Engineering T-Level covers heating, ventilation, air conditioning, lighting, and energy systems — preparing you for careers in sustainable construction and building services." },
        { keywords: ["design surveying", "design surveying and planning", "surveying t-level", "planning t-level"], answer: "Design, Surveying and Planning T-Level teaches civil engineering, surveying, building design, and planning — great for construction and property careers." },
        { keywords: ["accounting t-level", "accountancy t-level", "finance t-level"], answer: "Accounting T-Level provides training in financial accounting, management accounting, taxation, and bookkeeping — preparing you for careers in finance and accountancy." },
        { keywords: ["finance t-level", "banking t-level", "financial services t-level"], answer: "Finance T-Level covers financial services, investment management, insurance, and risk — opening doors to careers in banking, financial planning, and insurance." },
        { keywords: ["what courses", "what t-level courses", "available courses", "subjects", "list of t-levels"], answer: "T-Levels are available in: Digital Production Design & Development, Digital Support Services, Digital Business Services, Education & Childcare, Health, Healthcare Science, Building Services Engineering, Design Surveying & Planning, Accounting, Finance, and more." },

        // == Placements ==
        { keywords: ["aws cloud placement", "aws placement", "cloud support placement", "amazon placement london"], answer: "The AWS Cloud Support Placement is based in London. You'll gain hands-on experience supporting cloud infrastructure and AWS services. It typically lasts 45 days (minimum) and you'll be mentored by AWS professionals." },
        { keywords: ["developer placement", "software developer placement information", "developer opportunity manchester"], answer: "The Junior Software Developer placement in Manchester lets you work with real development teams building applications. You'll learn agile methodologies, coding standards, and version control." },
        { keywords: ["cyber security placement", "security placement birmingham", "cyber security assistant"], answer: "The Cyber Security Assistant placement in Birmingham focuses on security monitoring, threat detection, and vulnerability assessment. You'll work alongside experienced security analysts." },
        { keywords: ["helpdesk placement", "it helpdesk", "it support placement leeds"], answer: "The IT Helpdesk Technician placement in Leeds provides experience supporting users and maintaining IT systems. You'll develop troubleshooting and customer service skills." },
        { keywords: ["industry placement", "placement length", "how long placement", "placement hours", "45 days"], answer: "The minimum industry placement is 45 days (approximately 315 hours). Some placements may be longer depending on the course provider and employer. Placements can be full-time or part-time, block or day-release." },
        { keywords: ["placement opportunities", "where are placements", "placement locations"], answer: "Current placement locations include: London (AWS Cloud Support), Manchester (Junior Software Developer), Birmingham (Cyber Security Assistant), and Leeds (IT Helpdesk Technician). More placements are being added as the program grows." },

        // == Careers (salary-focused keywords to avoid overlap with placements) ==
        { keywords: ["how much does a software developer earn", "software developer salary", "how much do software developers make", "software developer pay"], answer: "Software Developers earn between £30,000 and £60,000 per year on average. The relevant T-Level is Digital Production, Design and Development." },
        { keywords: ["how much does a cloud engineer earn", "cloud engineer salary", "cloud engineer pay", "cloud engineer income"], answer: "Cloud Engineers earn between £35,000 and £70,000 per year. The relevant T-Level is Digital Support Services." },
        { keywords: ["how much does a cyber security analyst earn", "cyber security analyst salary", "cyber security pay"], answer: "Cyber Security Analysts earn between £35,000 and £65,000 per year. The relevant T-Level is Digital Business Services." },
        { keywords: ["how much does a teaching assistant earn", "teaching assistant salary", "teaching assistant pay"], answer: "Teaching Assistants earn between £20,000 and £30,000 per year. The relevant T-Level is Education and Childcare." },
        { keywords: ["how much does a healthcare assistant earn", "healthcare assistant salary"], answer: "Healthcare Assistants earn between £22,000 and £35,000 per year. The relevant T-Level is Health." },
        { keywords: ["how much does a laboratory technician earn", "laboratory technician salary", "lab tech salary"], answer: "Laboratory Technicians earn between £25,000 and £40,000 per year. The relevant T-Level is Healthcare Science." },
        { keywords: ["salary after t-level", "t-level salary", "how much can i earn", "starting salary"], answer: "T-Level graduates can expect starting salaries between £20,000 and £35,000, with experienced professionals earning £60,000+ in fields like software development, cloud engineering, and cyber security." },
        { keywords: ["career after t-level", "what can i do after", "after completion", "next steps"], answer: "After completing a T-Level, you can: (1) Enter skilled employment, (2) Start an apprenticeship or higher apprenticeship, (3) Study at university — T-Levels are UCAS-accepted, or (4) Do a higher technical qualification (HTQ)." },
        { keywords: ["career opportunities", "career pathways", "jobs after t-level"], answer: "Career paths include: Software Developer (£30k-£60k), Cloud Engineer (£35k-£70k), Cyber Security Analyst (£35k-£65k), Teaching Assistant (£20k-£30k), Healthcare Assistant (£22k-£35k), Laboratory Technician (£25k-£40k), and many more." },

        // == General ==
        { keywords: ["what are t-levels", "what is a t-level", "t-level meaning", "about t-levels"], answer: "T-Levels are 2-year qualifications in England that combine classroom learning with a substantial industry placement (minimum 45 days / 315 hours). They are equivalent to 3 A-Levels and are co-created with employers like Amazon, ensuring the skills you learn match real industry needs." },
        { keywords: ["how do i apply", "application process", "apply for t-level", "entry requirements"], answer: "To apply for a T-Level, find a local college or school offering your chosen course. Entry requirements typically include 5 GCSEs at grade 4 or above, including English and Maths. You can apply through UCAS or directly to the college. Some courses may have additional subject requirements." },
        { keywords: ["who is this for", "who is this portal for", "amazon t-level hub"], answer: "This Amazon T-Level Hub is primarily designed for German students, teachers, and parents who want to learn about T-Levels as a route into UK industries. It provides information in both English and German." },
        { keywords: ["german students", "germany", "international students", "abitur", "ausbildung"], answer: "For German students, T-Levels offer a blend of practical and academic learning similar to the Abitur or vocational Ausbildung, but with a direct route into UK industries. The qualification is increasingly recognised by German employers too. This portal includes a German language toggle to help you navigate in Deutsch." },
        { keywords: ["international", "visa", "international students t-level"], answer: "Yes, international students can study T-Levels in England. You'll need a suitable student visa and English language proficiency. The placement component is a great way to gain UK work experience. Contact individual colleges for specific international admissions guidance." },
        { keywords: ["compare to a-levels", "a-levels vs t-levels", "t-level vs a-level"], answer: "T-Levels are equivalent to 3 A-Levels. While A-Levels are more academic and theory-based, T-Levels focus on practical skills with 80% classroom learning and 20% industry placement (minimum 45 days). Both qualifications are UCAS-accepted for university entry." },
        { keywords: ["funding", "t-level cost", "free t-level", "financial support", "government funding"], answer: "T-Level funding is available through the UK government. For 16-19 year olds, the course is fully funded. There may be additional financial support available for travel, accommodation, equipment, and childcare through the Bursary Fund." },
        { keywords: ["university", "go to university", "ucas", "higher education", "degree after t-level"], answer: "Yes, T-Levels are accepted by many UK universities. The UCAS tariff points are equivalent to A-Levels. Some universities also value the industry placement experience highly in their admissions process. Check individual university entry requirements for specific courses." },
        { keywords: ["amazon role", "amazon t-levels", "what does amazon do"], answer: "Amazon is a key employer partner for T-Levels. We offer industry placements in cloud support (AWS), software development, and other digital roles. This portal helps you explore those opportunities and understand how T-Levels can lead to careers at Amazon and other top employers." },
        { keywords: ["employers", "which employers", "co-created", "industry partners"], answer: "T-Levels are co-created by over 250 leading employers including Amazon, Fujitsu, EDF Energy, the NHS, Microsoft, and many more — ensuring the skills you learn match real industry needs and giving you direct connections to potential employers." },
        { keywords: ["teachers", "schools", "educators", "curriculum"], answer: "This section is designed for German schools and educators advising students on international study options. It provides curriculum outlines, comparisons between T-Levels and German qualifications, and guidance on how schools can support students applying for T-Levels." },
        { keywords: ["parents", "guardians", "parent information"], answer: "For parents: T-Levels include robust safeguarding and supervision during placements. Progress is communicated regularly. The qualification is well-structured and supported, giving parents confidence that their child is in a safe, professional environment." },
        { keywords: ["accessibility", "language toggle", "german language", "font size", "high contrast"], answer: "This portal includes accessibility features: German/English language toggle, adjustable font sizing, high-contrast display options, link underlining, and screen-reader-friendly navigation — all accessible via the Settings menu." },
        { keywords: ["administration", "admin panel", "manage content", "reports"], answer: "The Administration section (for Amazon staff) allows managing website content, reviewing submitted forms and applications, and generating engagement reports on placement uptake and course interest." }
    ],
    de: [
        // == Kurse ==
        { keywords: ["digitale produktion", "digitale produktion design und entwicklung", "softwareentwicklung t-level"], answer: "Der T-Level Digitale Produktion, Design und Entwicklung umfasst Softwareentwicklung, Datenanalyse und digitales Design. Sie lernen Programmierung (Python, JavaScript), Projektmanagement und neue Technologien wie KI und Cloud-Computing." },
        { keywords: ["digitale unterstützungsdienste", "it-infrastruktur t-level"], answer: "Der T-Level Digitale Unterstützungsdienste konzentriert sich auf IT-Infrastruktur, Netzwerke, Cybersicherheit und Cloud-Computing – ideal für Rollen wie IT-Techniker oder Netzwerkingenieur." },
        { keywords: ["digitale geschäftsdienste", "datenmanagement t-level"], answer: "Der T-Level Digitale Geschäftsdienste umfasst Datenmanagement, Geschäftsanalyse und digitale Transformation – großartig für Karrieren in Datenanalyse und Business Intelligence." },
        { keywords: ["bildung und kinderbetreuung", "erziehung t-level"], answer: "Der T-Level Bildung und Kinderbetreuung bereitet Sie auf Rollen in der frühkindlichen Bildung, als Lehrassistent und in der Kinderbetreuungsverwaltung vor." },
        { keywords: ["gesundheit t-level", "krankenpflege t-level"], answer: "Der T-Level Gesundheit umfasst grundlegende Gesundheitskonzepte, Anatomie und Patientenversorgung. Er ist für Karrieren in der Krankenpflege, Notfallmedizin oder Gesundheitsunterstützung konzipiert." },
        { keywords: ["medizinwissenschaft", "labortechniker t-level"], answer: "Der T-Level Medizinwissenschaft konzentriert sich auf Laborwissenschaften, medizinische Physik und physiologische Wissenschaften – ideal für Labortechniker und klinische Wissenschaftler." },
        { keywords: ["gebäudetechnik", "bauwesen t-level"], answer: "Der T-Level Gebäudetechnik umfasst Heizung, Lüftung, Klimaanlage, Beleuchtung und Energiesysteme – Vorbereitung auf Karrieren in der Gebäudetechnik und nachhaltigem Bauen." },
        { keywords: ["design vermessung und planung", "vermessung t-level"], answer: "Der T-Level Design, Vermessung und Planung lehrt Tiefbau, Vermessung, Gebäudedesign und Planung – großartig für Karrieren im Bau- und Immobilienwesen." },
        { keywords: ["buchhaltung t-level", "finanzen t-level"], answer: "Der T-Level Buchhaltung bietet Ausbildung in Finanzbuchhaltung, Kostenrechnung, Steuerwesen und Buchführung – Vorbereitung auf Karrieren in Finanzen und Buchhaltung." },
        { keywords: ["finanzen t-level", "bankwesen t-level"], answer: "Der T-Level Finanzen umfasst Finanzdienstleistungen, Investmentmanagement, Versicherungen und Risikomanagement – öffnet Türen zu Karrieren im Bankwesen und in der Finanzplanung." },
        { keywords: ["welche kurse", "t-level kurse", "verfügbare kurse", "fächer"], answer: "T-Levels sind verfügbar in: Digitale Produktion Design & Entwicklung, Digitale Unterstützungsdienste, Digitale Geschäftsdienste, Bildung & Kinderbetreuung, Gesundheit, Medizinwissenschaft, Gebäudetechnik, Design Vermessung & Planung, Buchhaltung, Finanzen und mehr." },

        // == Praktika ==
        { keywords: ["aws cloud praktikum", "cloud support praktikum london", "amazon praktikum"], answer: "Das AWS Cloud-Support-Praktikum findet in London statt. Sie sammeln praktische Erfahrungen bei der Unterstützung von Cloud-Infrastruktur und AWS-Diensten. Es dauert mindestens 45 Tage und Sie werden von AWS-Profis betreut." },
        { keywords: ["softwareentwickler praktikum", "junior entwickler praktikum manchester"], answer: "Das Junior-Softwareentwickler-Praktikum in Manchester ermöglicht Ihnen die Zusammenarbeit mit echten Entwicklungsteams. Sie lernen agile Methoden, Codierungsstandards und Versionskontrolle." },
        { keywords: ["cybersicherheit praktikum", "sicherheit praktikum birmingham"], answer: "Das Cybersicherheits-Assistent-Praktikum in Birmingham konzentriert sich auf Sicherheitsüberwachung, Bedrohungserkennung und Schwachstellenbewertung." },
        { keywords: ["it helpdesk praktikum", "it-support praktikum leeds"], answer: "Das IT-Helpdesk-Techniker-Praktikum in Leeds bietet Erfahrung bei der Unterstützung von Benutzern und der Wartung von IT-Systemen." },
        { keywords: ["industriepraktikum", "praktikumsdauer", "wie lange praktikum", "45 tage"], answer: "Das Mindestindustriepraktikum beträgt 45 Tage (ca. 315 Stunden). Einige Praktika können je nach Kursanbieter und Arbeitgeber länger dauern." },
        { keywords: ["praktikumsplätze", "wo sind praktika", "praktikumsorte"], answer: "Aktuelle Praktikumsorte: London (AWS Cloud-Support), Manchester (Junior Softwareentwickler), Birmingham (Cybersicherheits-Assistent) und Leeds (IT-Helpdesk-Techniker)." },

        // == Karriere ==
        { keywords: ["softwareentwickler gehalt", "softwareentwickler karriere"], answer: "Softwareentwickler verdienen durchschnittlich zwischen £30.000 und £60.000 pro Jahr. Der relevante T-Level ist Digitale Produktion, Design und Entwicklung." },
        { keywords: ["cloud ingenieur gehalt", "cloud engineer gehalt"], answer: "Cloud-Ingenieure verdienen durchschnittlich zwischen £35.000 und £70.000 pro Jahr. Der relevante T-Level ist Digitale Unterstützungsdienste." },
        { keywords: ["cybersicherheitsanalyst gehalt"], answer: "Cybersicherheitsanalysten verdienen durchschnittlich zwischen £35.000 und £65.000 pro Jahr. Der relevante T-Level ist Digitale Geschäftsdienste." },
        { keywords: ["lehrassistent gehalt"], answer: "Lehrassistenten verdienen durchschnittlich zwischen £20.000 und £30.000 pro Jahr." },
        { keywords: ["gesundheitsassistent gehalt"], answer: "Gesundheitsassistenten verdienen durchschnittlich zwischen £22.000 und £35.000 pro Jahr." },
        { keywords: ["labortechniker gehalt"], answer: "Labortechniker verdienen durchschnittlich zwischen £25.000 und £40.000 pro Jahr." },
        { keywords: ["gehalt nach t-level", "einstiegsgehalt", "wie viel verdiene ich"], answer: "T-Level-Absolventen können mit Einstiegsgehältern zwischen £20.000 und £35.000 rechnen. Erfahrene Fachkräfte verdienen in Bereichen wie Softwareentwicklung und Cloud-Engineering £60.000+." },
        { keywords: ["karriere nach t-level", "was kann ich danach tun", "nach abschluss"], answer: "Nach Abschluss eines T-Levels können Sie: (1) Eine qualifizierte Beschäftigung aufnehmen, (2) Eine Ausbildung beginnen, (3) An einer Universität studieren (UCAS-anerkannt), oder (4) Eine höhere technische Qualifikation erwerben." },
        { keywords: ["karrieremöglichkeiten", "berufswege", "jobs nach t-level"], answer: "Karrierewege: Softwareentwickler (£30k-£60k), Cloud-Ingenieur (£35k-£70k), Cybersicherheitsanalyst (£35k-£65k), Lehrassistent (£20k-£30k) und viele mehr." },

        // == Allgemein ==
        { keywords: ["was sind t-levels", "was ist ein t-level", "t-level bedeutung", "über t-levels"], answer: "T-Levels sind 2-jährige Qualifikationen in England, die Unterricht mit einem umfangreichen Industriepraktikum (mindestens 45 Tage) kombinieren. Sie entsprechen 3 A-Levels und werden gemeinsam mit Arbeitgebern wie Amazon entwickelt." },
        { keywords: ["wie bewerbe ich mich", "bewerbungsprozess", "zulassungsvoraussetzungen"], answer: "Um sich zu bewerben, finden Sie ein College mit Ihrem Kurs. Voraussetzungen: 5 GCSEs der Note 4 oder höher, einschließlich Englisch und Mathe. Bewerbung über UCAS oder direkt beim College." },
        { keywords: ["für wen ist dieses portal", "amazon t-level hub"], answer: "Dieses Portal ist hauptsächlich für deutsche Schüler, Lehrer und Eltern konzipiert, die mehr über T-Levels als Weg in britische Industrien erfahren möchten." },
        { keywords: ["deutsche schüler", "deutschland", "abitur", "ausbildung"], answer: "Für deutsche Schüler bieten T-Levels eine Mischung aus praktischem und akademischem Lernen, ähnlich wie Abitur oder Ausbildung, mit direktem Weg in britische Industrien. Die Qualifikation wird zunehmend auch von deutschen Arbeitgebern anerkannt." },
        { keywords: ["internationale schüler", "visum", "englischkenntnisse"], answer: "Ja, internationale Schüler können T-Levels in England studieren. Sie benötigen ein Studentenvisum und Englischkenntnisse. Das Praktikum ist eine großartige Möglichkeit, britische Berufserfahrung zu sammeln." },
        { keywords: ["vergleich mit a-levels", "a-levels vs t-levels", "unterschied"], answer: "T-Levels entsprechen 3 A-Levels. A-Levels sind akademischer, T-Levels konzentrieren sich auf praktische Fähigkeiten mit 80% Unterricht und 20% Praktikum. Beide sind UCAS-anerkannt." },
        { keywords: ["finanzierung", "kosten t-level", "kostenlos", "staatliche förderung"], answer: "Die Finanzierung erfolgt durch die britische Regierung. Für 16- bis 19-Jährige ist der Kurs vollständig finanziert. Zusätzliche Unterstützung für Reise, Unterkunft und Ausrüstung ist verfügbar." },
        { keywords: ["universität", "studium nach t-level", "hochschule"], answer: "Ja, T-Levels werden von vielen britischen Universitäten anerkannt. Die UCAS-Punkte entsprechen A-Levels. Einige Universitäten schätzen die Praktikumserfahrung besonders." },
        { keywords: ["amazon rolle", "amazon t-levels", "was macht amazon"], answer: "Amazon ist ein wichtiger Arbeitgeberpartner für T-Levels. Wir bieten Praktika in Cloud-Support (AWS), Softwareentwicklung und anderen digitalen Bereichen an. Dieses Portal hilft Ihnen, diese Möglichkeiten zu erkunden." },
        { keywords: ["arbeitgeber", "welche arbeitgeber", "partner"], answer: "T-Levels werden von über 250 führenden Arbeitgebern mitentwickelt, darunter Amazon, Fujitsu, EDF Energy, der NHS und Microsoft." },
        { keywords: ["lehrer", "schulen", "pädagogen", "lehrplan"], answer: "Dieser Abschnitt bietet Lehrplanübersichten, Vergleiche mit deutschen Qualifikationen und Anleitungen, wie Schulen Schüler bei der Bewerbung unterstützen können." },
        { keywords: ["eltern", "erziehungsberechtigte"], answer: "Für Eltern: T-Levels beinhalten umfassende Schutzmaßnahmen und Betreuung während der Praktika. Der Fortschritt wird regelmäßig kommuniziert." },
        { keywords: ["barrierefreiheit", "sprachumschalter", "schriftgröße", "kontrast"], answer: "Dieses Portal bietet: Deutsch/Englisch-Umschalter, anpassbare Schriftgröße, kontrastreiche Anzeige und bildschirmleserfreundliche Navigation – alles über das Einstellungsmenü." },
        { keywords: ["verwaltung", "admin", "inhalte verwalten", "berichte"], answer: "Der Verwaltungsbereich (für Amazon-Mitarbeiter) ermöglicht die Verwaltung von Website-Inhalten, die Überprüfung eingereichter Formulare und die Erstellung von Engagement-Berichten." }
    ]
};

// --- Knowledge matcher ---
function buildKnowledgeIndex() {
    const index = {};
    for (const lang of ["en", "de"]) {
        index[lang] = [];
        for (const entry of chatbotKnowledge[lang]) {
            for (const kw of entry.keywords) {
                index[lang].push({ phrase: kw.toLowerCase(), answer: entry.answer });
            }
        }
    }
    return index;
}

const knowledgeIndex = buildKnowledgeIndex();

function findBestAnswer(userMessage, lang) {
    const q = userMessage.toLowerCase().trim();
    const kb = knowledgeIndex[lang] || knowledgeIndex["en"];

    // Score each entry by how many keyword words match
    let bestScore = 0;
    let bestAnswer = null;

    for (const entry of kb) {
        const phraseWords = entry.phrase.split(/\s+/);
        let score = 0;
        let matched = 0;
        for (const word of phraseWords) {
            if (word.length < 3) continue;
            if (q.includes(word)) {
                score += word.length;
                matched++;
            }
        }
        // Bonus if the full phrase is contained
        if (q.includes(entry.phrase)) {
            score += entry.phrase.length * 3;
        }
        // Bonus for match ratio
        if (phraseWords.length > 0 && matched / phraseWords.length > 0.5) {
            score *= 1.5;
        }
        if (score > bestScore) {
            bestScore = score;
            bestAnswer = entry.answer;
        }
    }

    // Fallback responses
    if (bestScore < 3) {
        const greetingEn = /^hi|^hello|^hey|^good|^morning|^afternoon|^evening|how are you|what's up|^yo\b/.test(q);
        const greetingDe = /^hallo|^hi|^hey|guten |morgen|tag |grüß|servus|wie geht/.test(q);
        const thanks = /thank|thanks|cheers|danke|vielen|dank/.test(q);
        const goodbye = /bye|goodbye|see you|tschüss|auf wiedersehen|wiedersehen/.test(q);
        const help = /what can you|help|capabilities|what do you do|was kannst du|hilfe|funktionen/.test(q);

        if (lang === "de") {
            if (greetingDe || (greetingEn && !thanks && !goodbye))
                return "Hallo! Willkommen im Amazon T-Level-Hub. Ich kann Fragen zu T-Level-Kursen, Industriepraktika, Karrieremöglichkeiten, Bewerbungen und mehr beantworten. Wie kann ich Ihnen heute helfen?";
            if (thanks) return "Gern geschehen! Wenn Sie weitere Fragen zu T-Levels haben, fragen Sie einfach.";
            if (goodbye) return "Auf Wiedersehen! Kommen Sie jederzeit zurück, wenn Sie weitere Fragen zu T-Levels haben.";
            if (help) return "Ich kann Fragen beantworten zu: T-Level-Kursen (Digital, Gesundheit, Bauwesen usw.), Industriepraktika (AWS Cloud, Softwareentwicklung, Cybersicherheit), Karrierewegen und Gehältern, dem Bewerbungsprozess, Finanzierung und Universitätszugang. Fragen Sie einfach!";
            return "Ich bin mir nicht sicher. Versuchen Sie nach Kursen (z.B. Digitale Produktion), Praktika, Gehältern oder der Bewerbung zu fragen. Ich bin hier, um zu helfen!";
        }

        if (greetingEn || greetingDe)
            return "Hello! Welcome to the Amazon T-Level Hub. I can answer questions about T-Level courses, industry placements, career opportunities, applications, and more. How can I help you today?";
        if (thanks) return "You're welcome! If you have any more questions about T-Levels, feel free to ask.";
        if (goodbye) return "Goodbye! Feel free to return anytime if you have more questions about T-Levels.";
        if (help) return "I can answer questions about: T-Level courses (Digital, Health, Construction, etc.), industry placements (AWS Cloud, Software Dev, Cyber Security), career pathways and salaries, the application process, how T-Levels compare to A-Levels, funding, university progression, and international student information. Just ask!";

        return "I'm not sure I have a specific answer for that. Try asking about: T-Level courses (like Digital Production or Health), industry placements, career salaries, how to apply, or what T-Levels are. I'm here to help!";
    }

    return bestAnswer;
}

// --- Chatbot UI Logic ---
const chatbotToggle = document.getElementById("chatbotToggle");
const chatbotPanel = document.getElementById("chatbotPanel");
const chatbotClose = document.getElementById("chatbotClose");
const chatbotMessages = document.getElementById("chatbotMessages");
const chatbotInput = document.getElementById("chatbotInput");
const chatbotSend = document.getElementById("chatbotSend");

const quickReplyOptions = {
    en: [
        { label: "What are T-Levels?", query: "What are T-Levels?" },
        { label: "Show me available courses", query: "What courses are available?" },
        { label: "Tell me about placements", query: "Tell me about industry placements" },
        { label: "How do I apply?", query: "How do I apply for a T-Level?" },
        { label: "What careers are available?", query: "What career opportunities are there after a T-Level?" },
        { label: "Funding & costs", query: "Is there funding available for T-Levels?" },
    ],
    de: [
        { label: "Was sind T-Levels?", query: "Was sind T-Levels?" },
        { label: "Verfügbare Kurse anzeigen", query: "Welche Kurse sind verfügbar?" },
        { label: "Über Praktika", query: "Erzählen Sie mir über Industriepraktika" },
        { label: "Wie bewerbe ich mich?", query: "Wie bewerbe ich mich für einen T-Level?" },
        { label: "Karrieremöglichkeiten", query: "Welche Karrieremöglichkeiten gibt es nach einem T-Level?" },
        { label: "Finanzierung & Kosten", query: "Gibt es Förderungen für T-Levels?" },
    ]
};

function showQuickReplies() {
    const lang = localStorage.getItem("language") || "en";
    const container = document.getElementById("quickReplies");
    if (!container) return;
    const options = quickReplyOptions[lang] || quickReplyOptions["en"];
    container.innerHTML = options.map(opt => `
        <button onclick="handleQuickReply('${opt.query.replace(/'/g, "\\'")}')" style="
            background: white;
            border: 1.5px solid #ff9900;
            border-radius: 20px;
            padding: 9px 14px;
            font-size: 13px;
            color: #232f3e;
            cursor: pointer;
            text-align: left;
            transition: background .2s, color .2s;
            font-family: Arial, sans-serif;
        " onmouseover="this.style.background='#ff9900';this.style.color='white'"
           onmouseout="this.style.background='white';this.style.color='#232f3e'">
            ${opt.label}
        </button>
    `).join('');
    container.style.display = 'flex';
}

function hideQuickReplies() {
    const container = document.getElementById("quickReplies");
    if (container) container.style.display = 'none';
}

function handleQuickReply(query) {
    hideQuickReplies();
    handleUserQuery(query);
}
function openChatbot() {
    chatbotPanel.classList.add("active");
    showQuickReplies();
}

function closeChatbot() {
    chatbotPanel.classList.remove("active");
}

function addBotMessage(text) {
    const div = document.createElement("div");
    div.className = "chatbot-message bot";
    div.textContent = text;
    chatbotMessages.appendChild(div);
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
}

function addUserMessage(text) {
    const div = document.createElement("div");
    div.className = "chatbot-message user";
    div.textContent = text;
    chatbotMessages.appendChild(div);
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
}

function addTypingIndicator() {
    const div = document.createElement("div");
    div.className = "chatbot-message typing";
    div.id = "typingIndicator";
    div.textContent = "Thinking...";
    chatbotMessages.appendChild(div);
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    return div;
}

function removeTypingIndicator() {
    const indicator = document.getElementById("typingIndicator");
    if (indicator) indicator.remove();
}

function handleUserQuery(query) {
    const text = query.trim();
    if (!text) return;

    hideQuickReplies();
    addUserMessage(text);
    chatbotInput.value = "";
    addTypingIndicator();

    const lang = localStorage.getItem("language") || "en";

    // Use local knowledge base (fully self-contained, no server needed)
    setTimeout(() => {
        removeTypingIndicator();
        const answer = findBestAnswer(text, lang);
        addBotMessage(answer);
    }, 300 + Math.random() * 400);
}

// Event listeners
chatbotToggle.addEventListener("click", openChatbot);
chatbotClose.addEventListener("click", closeChatbot);

chatbotSend.addEventListener("click", function () {
    handleUserQuery(chatbotInput.value);
});

chatbotInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
        e.preventDefault();
        handleUserQuery(chatbotInput.value);
    }
});

// Open chatbot from the nav link (Chatbot button in top nav)
document.querySelector('[data-i18n="chatbot"]')?.addEventListener("click", function (e) {
    e.preventDefault();
    openChatbot();
});

function setScrollOffset() {
    const stickyNav = document.querySelector('.sticky-nav');
    if (stickyNav) {
        document.documentElement.style.scrollPaddingTop = stickyNav.offsetHeight + 'px';
    }
}

setScrollOffset();
window.addEventListener('resize', setScrollOffset);

// === SCROLL REVEAL ===
const revealSections = document.querySelectorAll('.info-section');

const revealObserver = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, {
    threshold: 0.1
});

revealSections.forEach(function(section) {
    revealObserver.observe(section);
});


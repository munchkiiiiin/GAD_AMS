-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: May 29, 2026 at 10:33 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gad_submission_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `accomplishment_report`
--

CREATE TABLE `accomplishment_report` (
  `ar_id` int(11) NOT NULL,
  `control_number` varchar(20) NOT NULL,
  `total_participants` int(11) DEFAULT NULL,
  `male_participants` int(11) DEFAULT NULL,
  `female_participants` int(11) DEFAULT NULL,
  `activity_rating` decimal(5,2) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `attachments_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_design`
--

CREATE TABLE `activity_design` (
  `ad_id` int(11) NOT NULL,
  `act_title` varchar(255) NOT NULL,
  `form_type` enum('inset','extension','employee') DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `target_participants` int(11) DEFAULT NULL,
  `proposed_budget` decimal(15,2) DEFAULT NULL,
  `office_unit` varchar(255) DEFAULT NULL,
  `attachment_path` varchar(500) DEFAULT NULL,
  `control_number` varchar(20) DEFAULT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_design`
--

INSERT INTO `activity_design` (`ad_id`, `act_title`, `form_type`, `status_id`, `user_id`, `start_date`, `end_date`, `target_participants`, `proposed_budget`, `office_unit`, `attachment_path`, `control_number`, `venue`, `created_at`) VALUES
(3, 'testing1', 'inset', 1, 2, '2026-05-23', '2026-05-31', 523, 25000.00, 'GAD Office', 'uploads/drafts/activity_designs/1780021568_0d528171a9d6d53e78d7.pdf', NULL, 'hshhhdhdhd', '2026-05-29 02:26:08'),
(4, 'testing 2', 'inset', 1, 2, '2026-05-21', '2026-05-31', 523, 25000.00, 'GAD Office', 'uploads/drafts/activity_designs/1780022095_62e44e68a51862f12d57.pdf', NULL, 'hhhhhhhh', '2026-05-29 02:34:55'),
(5, 'bbbbbb', 'extension', 1, 2, '2026-05-30', '2026-05-31', 523, 26000.00, 'GAD Office', 'uploads/drafts/activity_designs/1780025806_dfbe9a5d9aa03216354a.pdf', NULL, 'bbbbbbb', '2026-05-29 03:36:46');

-- --------------------------------------------------------

--
-- Table structure for table `activity_statuses`
--

CREATE TABLE `activity_statuses` (
  `id` int(11) NOT NULL,
  `status_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_statuses`
--

INSERT INTO `activity_statuses` (`id`, `status_name`) VALUES
(2, 'Approved'),
(4, 'Archived'),
(1, 'Pending'),
(3, 'Revision Required');

-- --------------------------------------------------------

--
-- Table structure for table `gad_plan_budget`
--

CREATE TABLE `gad_plan_budget` (
  `gpb_id` int(11) NOT NULL,
  `gender_issue_mandate` varchar(255) DEFAULT NULL,
  `cause_of_gender_issue` text DEFAULT NULL,
  `gad_result_objective` text DEFAULT NULL,
  `relevant_org_mfo_pap` text DEFAULT NULL,
  `gad_activity` varchar(255) DEFAULT NULL,
  `performance_indicators_targets` text DEFAULT NULL,
  `gad_budget` decimal(15,2) DEFAULT NULL,
  `budget_breakdown` text DEFAULT NULL,
  `source_of_budget` varchar(100) DEFAULT NULL,
  `responsible_unit_office` varchar(100) NOT NULL,
  `form_type` enum('client-focused activity','organization-focused activity','attributed program') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gad_plan_budget`
--

INSERT INTO `gad_plan_budget` (`gpb_id`, `gender_issue_mandate`, `cause_of_gender_issue`, `gad_result_objective`, `relevant_org_mfo_pap`, `gad_activity`, `performance_indicators_targets`, `gad_budget`, `budget_breakdown`, `source_of_budget`, `responsible_unit_office`, `form_type`) VALUES
(1, 'Republic Act No. 10931, Universal Access to Quality Tertiary Education Act Section 8 on Affirmative Action Program; CHED Memorandum Orders on GAD Mainstreaming in Higher Education Institutions (HEIs)', 'Extraordinary life situations due to disasters, calamities, and socio-cultural discrimination', 'To promote equitable access and participation of both women and men from GIDAs in tertiary education through gender-responsive implementation of the Affirmative Action Agenda.', 'MFO: Higher Education Program', 'Implementation of Affirmative Action Agenda', 'Number of served disadvantaged students - 100% disadvantaged students', 700000.00, 'PS Attribution 500,000.00 Supplies and Materials 200,000.00', 'GAA GAA', 'OSS', 'client-focused activity'),
(2, 'Republic Act No. 10931, Universal Access to Quality Tertiary Education Act Section 8 on Affirmative Action Program; CHED Memorandum Orders on GAD Mainstreaming in Higher Education Institutions (HEIs)', 'High tuition and miscellaneous fees, compounded by socio-cultural expectations for women to prioritize domestic roles over education', 'To promote gender equality in access to tertiary education by eliminating financial barriers for both male and female students.', 'MFO: Higher Education Program', 'Provision of free tuition fee under RA 10931 to eligible male and female students of the university', 'Percentage of qualified students granted free tuition - 100% of qualified students granted free tuition.', 131100000.00, 'Tuition Fee 131,100,000.00', 'GAA', 'OSS, OUR, UHS', 'client-focused activity'),
(3, 'CHED Memorandum Order No. 01 series 2015', 'Limited activities to increase awareness of men and women students to GAD-related information (1st year students, transferees)', 'To increase the students level of awareness and appreciation on GAD', 'MFO: Higher Education Program', 'Conduct GAD orientation/ forum/ seminar to BSU 1st year/ transferees students (face to face/ online: 14 colleges)', 'No. of students oriented on GAD - 4,000 students oriented on GAD (F:2750 M:1250)', 453363.26, 'Meals and Snack 318,800.00 Supplies and Materials 10,000.00 PS Attribution 124,563.26', 'GAA GAA GAA', 'OSS, GAD Office, 3 Campuses (La Trinidad, Bokod & Buguias Campus)', 'client-focused activity'),
(4, 'CHED Memorandum Order No. 01 series 2015', 'Student leaders have limited understanding on GAD in the University', 'To empower student leaders regarding GAD responsive leadership (La Trinidad Campus, Bokod Campus and Buguias Campus)', 'MFO: Higher Education Program', 'Continuous conduct of GAD responsive leadership training for student', 'No. of training conducted to increase GAD awareness and responsiveness of students leaders - 2 training (Female:200 Male:100) (La Trinidad Campus, Bokod Campus and Buguias Campus)', 150000.00, 'Supplies and Materials 30,000.00 Snack 20,000.00 PS Attribution 100,000.00', 'GAA GAA GAA', 'OSS', 'client-focused activity'),
(5, 'Part VII of CHED Memorandum Order Number 1, series 2015 on Gender-Responsive Research and Extension Program', 'Presence of gender inequality, poverty and GAD-related concerns in the community', 'To sustain GAD-related extension activities delivering technology transfer, Livelihood Program, Technical Assistance, and Advocacy to community partners to help promote gender equality, poverty reduction and sustainable developmen', 'MFO: Extension Services- Research Services- Advance Education ServicesHigher Education Services', 'Conduct of Extension project/ activities to partner organizational/ communities as component of Gender Responsive Extension Program (GREP) to partner organization/ communities', 'No. of extension activities conducted within the year - 24 Extension program/project/ activities conducted within the year (Female:560 Male: 500)', 3500000.00, 'Seminar Package/Meals & Snacks/ Fuel for Transportation/ Vehicle Rental/ Other Professional Services 2,500,000.00 PS Attribution 1,000,000.00', 'GAA GAA', 'Research and Extension, various offices/ colleges in the University/ external campuses', 'client-focused activity'),
(6, 'Limited access of PWDs to gender-responsive programs and services/DBM-DSWD Joint Circular No. 2003-01 provides guidelines for the implementation of Section 29 of the General Appropriations Act (GAA), requiring government agencies to set aside at least 1% ', 'Limited access of PWDs to gender-responsive programs and services', 'Improved access of PWDs to gender-responsive, inclusive, and empowering programs and services.', 'MFO: Research Services- Extension Services-Advance Education ServicesHigher Education Services', 'Awareness of women PWDs who benefited from the program', 'Number of women PWDs who benefited from the program - FM No. of GAD program/project/ activityprovided for PWD - At least 1 program/project/ activity', 350000.00, 'PS Attribution 100,000.00 Supplies & Materials/ Meals & Snacks 250,000.00', 'GAA GAA', 'HRMO, OSS', 'client-focused activity'),
(7, 'Lack of senior citizens access to gender-responsive programs and services/DBM-DSWD Joint Circular No. 2003-01 provides guidelines for the implementation of Section 29 of the General Appropriations Act (GAA), requiring government agencies to set aside at l', 'Absence of sustainable and gender-responsive university programs for senior citizens', 'Improved access of senior citizensto gender-responsive, inclusive, and empowering programs and services', 'MFO: Research Services- Extension Services- Advance Education ServicesHigher Education Services', 'Program: BSU Kalinga for women Senior Citizens', 'Number of Programs provided for Senior Citizens - At least 1 program for BSU Number of women senior citizens who benefited from the program - F21 M23', 250000.00, 'Supplies & Materials/ Meals & Snacks 250,000.00', 'GAA', 'GAD Office, Colleges, External Campuses', 'client-focused activity'),
(8, 'Low number of women\'s participation in sports/MCW-IRR Section 14 Develop, establish and strengthen programs for the participation of women in competitive and non-competitive sports as means to achieve excellence, promote physical and social well-being', 'Minimal attendance of female students to competitive and non-competitive sports', 'To increase female students level of participation and awareness on Gender in Sports', 'MFO: Higher Education Program', 'Participate in sports activities targeted for female students', 'No. of sports activities supported through allocation of budget for sports and socio- cultural activities/ E-sports (i.e. Annual Women\'s Martial Arts Festival- 2 sports activities (Female:20)', 160000.00, 'Registration & Travelling Expenses 80,000.00 Meals and Snacks 60,000.00 PS Attribution 20,000.00', 'GAA GAA GAA', 'CHK', 'client-focused activity'),
(9, 'Programs on Awards and Incentives for Service Excellence (PRAISE under CSC Res.No.010112 and CSC MC No.1,s. 2001); Memo Circular No.2011-01 (Guidelines for the Creation, Strengthening and Institutionalization of GAD Focal Point System: Roles and Responsib', 'Low recognition/ appreciation on the Gender Mainstreaming in BSU', 'Strengthen Gender Mainstreaming through recognition of GAD implementation in the University', 'MFO: Research Services-Extension Services- Advance Education ServicesHigher Education Services', 'Provide recognition and award to GAD implementer and other GAD-related award (GAD implementer for students and employees, GAD Advocate Award)', 'No. of award will be provided through BSU-PRAISE- At least 1 GAD Advocate award will be provided through BSU-PRAISE', 205000.00, 'Incentive GAD Advocate Award 5,000.00 PS Attribution 200,000.00', 'GAA GAA', 'HRDO, HRMO, BSU-PRAISE Committee, GAD Office', 'organization-focused activity'),
(10, 'Limited application of GAD Mainstreaming (GM) in Instruction, Research, Extension and Production/Magna Carta of Women (RA 9710)', 'Low awareness among personnel in the University about GAD mainstreaming', 'To enhance GAD mainstreaming in Administration, Academic, Research and Extension, Production', 'MFO: Research Services- Extension Services- Advance Education ServicesHigher Education Services', 'Conduct GAD related Gender Mainstreaming capability building and competency acquisition', 'No. of training/workshop/ seminars conducted - 25 training/ workshop/ seminars/ Learning and Development (F:1500 M: 1000)', 4000000.00, 'Seminar Package/Meals & Snacks/ Fuel for Transportation/ Vehicle Rental/ Professional Services (La Trinidad Bokod and Buguias Campus) 3,500,000.00 PS Attribution 500,000.00', 'GAA GAA', 'GAD Office, HRDO, Research and Extension, OQAA, All Colleges with External Campuses', 'organization-focused activity'),
(11, 'Magna Carta of Women IRR Section 37 Gender Mainstreaming as a Strategy for Implementing the Magna Carta of Women', 'Productivity of employees affected due to filial obligations, affecting promotion of women to higher positions or from participating in capability enhancement sessions', 'Inadequate support services to personnel and students with children', 'MFO: Research ServicesExtension Services Advance Education ServicesHigher Education Services', 'Operationalize of BSU College of Nursing Reproductive Health Care Center', 'No. of maintained Reproductive Health Care Center- 1 maintained BSU CN Reproductive Health Care Center', 120000.00, 'Supplies and Materials 20,000.00 PS Attribution 100,000.00', 'GAA GAA', 'College of Nursing', 'organization-focused activity'),
(12, 'Executive Order No. 340 s. 1997 Directing National Government Agencies and Government-Owned and-Controlled Corporations to provide Day Care Services for their Employee\'s Children under five years of age', 'Problems of parents and students related to child care', 'Ensure opportunities of personnel and students to have access on agency care services to children to avoid absenteeism', 'MFO: Research ServicesExtension Services Advance Education ServicesHigher Education Services', 'Maintenance of Child Minding Center for working parents in ensuring that they have a safe place to leave their child while they are at their work places', 'No. of established child minding center- Fully maintained new established and existing child minding centers at BSU La Trinidad,Bokod Campus and Buguias Campus', 230000.00, 'Supplies and Materials 130,000.00 PS Attribution 100,000.00', 'GAA GAA', 'GAD Office, External Campuses', 'organization-focused activity'),
(13, 'Magna Carta of Women (RA 9710)', 'Low integration of gender mainstreaming of BSU', 'To strengthen the GAD integration in the operations of BSU', 'MFO: Research ServicesExtension Services Advance Education ServicesHigher Education Services', 'Create a Monitoring Team to conduct monitoring and evaluation of the utilization/ outcome of GAD PAPs and ensure effectiveness of the GAD PAPs', 'No. of monitoring and assessment meetings with reports conducted4 monitoring and assessment meetings with reports conducted', 330000.00, 'Supplies and Materials 10,000.00 PS Attribution 320,000.00', 'GAA GAA', 'GAD Office', 'organization-focused activity'),
(14, 'Magna Carta of Women IRR Section 37 C. Creation and/or Strengthening of the GAD Focal Points (GFPs)', 'Low level of capacity of GFPS to develop and implement GAD programs and activities due to new members', 'Capacitated GFPS members in order to implement GAD PAP\'s and advance GAD Mainstreaming (GM) in the University', 'MFO: Research ServicesExtension Services Advance Education ServicesHigher Education Services', 'for GFPS/Secretariat: GMEF/HGDG/GPB/GAD Agenda/GAD Deepening Session and TOT among other related trainings and capacity building activities (Regional/ National GAD-related trainings/ seminars/ forum/workshop)', 'No. training/ seminars/ workshop attendance for each GFPS-members on GAD related updates and mandates- At least 1 Training/ seminar/ workshop attendance for each GFPS-members on GAD related updates and mandates (Female: 31, Male: 15)', 896000.00, 'GFPS TWG PAPs 396,000.00 PS Attribution 500,000.00', 'GAA GAA', 'GAD Office', 'organization-focused activity'),
(15, 'Section 37-C2 Rule VI of the Magna Carta of Women\'s IRR on duties and function of the GAD Focal Point System/Magna Carta of Women (RA 9710)', 'Compliance to provisions regarding regular monitoring of gender mainstreaming efforts', 'To ensure operations of GAD Office as well as monitor and evaluate GM efforts of the University', 'MFO: Research ServicesExtension Services Advance Education ServicesHigher Education Services', 'Regular coordination and meetings of GAD-GFPS (Execom, GFPS- TWG members and external campus TWG members) and emergency meeting when necessary', 'No. of reports on regular meetings per campus conducted will be available at the end of the year - At least 6 reports on regular meetings conducted will be available at the end of the year, RGADC quarterly meeting/s', 211720.00, 'Meals & Snack 111,720.00 PS Attribution 100,000.00', 'GAA GAA', 'GAD Office', 'organization-focused activity'),
(16, 'Duties and function of the GAD Focal Point System/CHED Memo 2015-1', 'No plantilla personnel assigned to plan, implement and monitor GAD PAPs on a full-time basis', 'To ensure operations of GAD Office as well as monitor and evaluate GM efforts of the University', 'MFO: Research ServicesExtension Services Advance Education ServicesHigher Education Services', 'Engage support staff to assist in the implementation of GFPS PPA\'s and Gender Mainstreaming in the university through rehiring of GAD staff and Student Assistant', 'Salary of GAD Staff: Casual No. of rehired personnel (casual) and student assistant -At least two (2) staff renewed/rehired (Casual) and at least one (3) Student Assistant/ SPES per semester', 550000.00, 'Salary of GAD Staff and SPES/ Student Assistant 550,000.00', 'GAA', 'GAD Office', 'organization-focused activity'),
(17, 'Low level of Awareness on Gender Mainstreaming (GM) in Instruction, Research, Extension and\nProduction among newly hired personnel/Magna of Women (RA 9710), CHED Memo 2015-1', 'Lack of regular orientation and refresher training on gender sensitivity and GAD mandates', 'To enhance awareness and understanding of gender concepts, GAD mandates, and gender-responsive work practices among newly hired and current personnel', 'MFO: Conduct Gender Sensitivity Training (GST) for newly hired and current personnel (continuing activity)', 'Conduct Gender Sensitivity Training (GST) for newly hired and current personnel (continuing activity)', 'No. of training conducted for newly hired personnel and refresher trainings for current personnel - 1 training conducted for at least 100% of newly hired personnel and 3 refresher trainings for current personnel', 421728.32, 'Meals & Snack 167,200.00 Token 4,000.00 Professional Fee 144,528.32 Supplies and Materials 6,000.00 PS Attribution 100,000.00', 'GAA GAA GAA GAA GAA', 'GAD Office', 'organization-focused activity'),
(18, 'Part V, Rule II, Section 4 of CHED Memorandum Order No. 1 Series of 2015/CHED Memo 2015-1', 'Limited number of GAD library and related learning materials across various discipline', 'To increase the provision of adequate and accessible library and related learning materials across various disciplines and educational levels', 'MFO: Research ServicesExtension Services Advance Education ServicesHigher Education Services', 'Provision of knowledge products (books magazine, multi-media) for adequate and accessible library and related learning materials in support to gender-responsive Curriculum Programs', 'No. of procured library and learning materials- 200 books', 2600000.00, 'Books and Instructional Materials 2,500,000.00 PS Attribution 100,000.00', 'GAA GAA', 'ULIS', 'organization-focused activity'),
(19, 'Development and Dissemination of Gender and Development (GAD) Information, Education, and Communication (IEC) Materials', 'Presence of Gender Based Violence (GBV) issues/reports/cases in the university', 'Institutionalize GAD mechanisms in the University and sustain awareness campaigns on sexual harassment and gender-based violence', 'MFO: Research ServicesExtension Services Advance Education ServicesHigher Education Services', 'Development and Dissemination of Gender and Development (GAD) Information, Education, and Communication (IEC) Materials', 'No. of Communication and IEC materials/knowledge products - Official Publication of BSU with GAD articles, pictures as a medium for employees, clients, students partners to disseminate programs, achievement and advocacies, Maintained GAD Bulletin board - At least 8 GAD Bulletin board ,Sector-specific knowledge products on GAD generated and designed to be downloadable via BSU website - At least 2', 296000.00, 'Shamag 96,000.00 PS Attribution 100,000.00 Supplies and Materials 100,000.00', 'GAA GAA GAA', 'UPAO, GAD Office', 'organization-focused activity'),
(20, 'Institutionalizing GAD database and Sex-Disaggregated Database/Magna Carta of Women (RA 9710), Section 36 on Sex-Disaggregated Database', 'Minimal awareness and appreciation on the relevance of the centralized Sex-Disaggregated database', 'To establish a centralized GAD-related database of the University', 'MFO: Research ServicesExtension Services Advance Education ServicesHigher Education Services', 'Updating of Sex-Disaggregated Data (SDD) and other data related to personnel/students/clients GAD-related database for gender analysis and report preparation', 'No. of well-organized and maintained GAD database for easy reference and access 1 GAD databaseEstablishment of GAD-Database system per college/unit.', 1100000.00, 'PS Attribution 100,000.00 Maintenance of SDD/ Internet connection 1,000,000.00', 'GAA GAA', 'ICT, GAD Office', 'organization-focused activity'),
(21, 'Magna Carta for women, Chapter IV: Section 10 and RA 10121, Section 2 & 9', 'Limited resources of the DSWD and LGU to provide for students who are transient residents and limited appreciation on women\'s role in nation building among employees and students, especially new ones', 'To ensure that disaster assistance provided to distressed students are gender-responsive', 'MFO: MFO: Research ServicesMFO: Extension Services MFO: Advance Education ServicesMFO: Higher Education Services', 'Provision of gender-responsive services to employees and students who experienced crisis/ disaster (e.g. Distribution of hygiene kits for both women and men)', 'No. of pax of the most affected employees/ students during crises - 1,000 employees/students', 211200.00, 'Crisis pack: Php,200/ pack X 1,000.00 pax 200,000.00 PS TWG Members 10,000.00', 'GAA GAA', 'GAD Office, HDRO, NSTP, various offices/ all colleges in the University', 'organization-focused activity'),
(22, 'Compliance to Section 18 of MCW RA 9710:Special Leave Benefits for Women RA 8187: Paternity Leave Section 8 of RA 8972:Solo Parents Welfare Act of 2000 Section 43 of RA 9262: Anti-Violence Against Women and Their Children Act of 2004)', 'Employees may require special leaves due to parental obligations, health concerns and other circumstances that may require the need thereof', 'Enhanced support services for employees in need of special leaves', 'MFO: MFO: Research ServicesMFO: Extension Services MFO: Advance Education ServicesMFO: Higher Education Services', 'Provision of gender leaves and conduct of Seminar on Gender Related Leaves for Newly Hired Employees', 'No. of Maternity, Paternity, Solo parent, gynecological, VAWC leave of employees who will avail and 1 Seminar conducted (M:20 F:50)- All (100%) Maternity, Paternity, Solo parent, gynecological, VAWC leave of employees who will avail and 1 Seminar conducted (M:20 F:50)', 1000000.00, 'PS Attribution 1,000,000.00', 'GAA', 'HRMO, CBOO, various offices/ colleges in the University', 'organization-focused activity'),
(23, 'Compliance to Proclamation 227 on the observance of Women\'s Role in History Month and Proclamation 1172, s. 2006 on the 18-Day Campaign to End Violence Against Women (VAW)', 'The need to highlight women\'s rights, their role in national development/ nation building and need to provide platform to invoke protection of women\'s rights against VAW, gender-based violence, Safe Spaces Act (RA No. 11313) and concerns that affect women and men', 'To strengthen awareness of BSU students/ employees on women\'s rights and their role in national development and nation building', 'MFO: Research ServicesExtension Services Advance Education ServicesHigher Education Services', 'Participation to18-Day Campaign to end VAW and Women\'s Month Celebration/ activities and programs organized by PCW and other agency/ies', 'No. of activities conducted per campus - At least one (1) activity conducted per campus', 450001.00, 'At least one (1) activity conducted per campus 250,000.00 PS Attribution 200,000.00', 'GAA GAA', 'GAD Office, various offices/ colleges in the University/ external campus', 'organization-focused activity'),
(24, 'Productivity of employees affected due to filial obligations, affecting promotion of women to higher positions or from participating in capability enhancement sessions/Magna Carta of Women IRR Section 37 Gender Mainstreaming as a Strategy for Implementing', 'Inadequate support services for personnel/students with young children and breastfeeding mothers (affecting productivity, especially among women non implementation of RA 10028)', 'Inadequate support services to personnel and students with children', 'MFO: Research ServicesExtension Services Advance Education ServicesHigher Education Services', 'Establishment/ maintenance of breastfeeding station established in the preceding years', 'Fully maintained Lactation rooms - 100% fully maintained lactation rooms at BSU La Trinidad, Bokod and BuguiasCampus', 220000.00, 'Supplies and Materials 50,000.00 PS Attribution 170,000.00', 'GAA GAA', 'GAD Office, External Campuses', 'organization-focused activity'),
(25, 'Low level of employees understanding of gender issues/ concept to promote gender equality and a gender-responsive work environment./RA 9710 (Magna Carta of Women), PCWNEDADBM Joint Circular 2012-01, and CSC MC No. 12 s. 2005, the University shall conduct ', 'Lack of regular gender-related capacity-building activities and insufficient integration of gender sensitivity in employee development programs', 'To enhance the gender awareness and sensitivity of BSU employees, enabling them to recognize and eliminate gender bias and stereotyping, and to foster a gender-responsive and equitable workplace', 'MFO: Research ServicesExtension Services Advance Education ServicesHigher Education Services', 'Conduct of gender sensitivity orientations for BSU Personnel (continuing activity)', 'No. of training conducted for BSU personnel -At least 3 training to be conducted', 253796.24, 'Supplies and Materials 10,000.00 Meals & Snack/ Professional Fee 233,796.24 PS 10,000.00', 'GAA GAA GAA', 'GAD Office', 'organization-focused activity'),
(26, 'Establishment of Gender-Responsive Curricular Programs/Part V of CMO 01, s. 2015/ CHED Memo 2015-1', 'Limited subject for GAD Integration of Gender-Responsive Instruction and Curriculum Developmen', 'Integration of gender mainstreaming in curriculum/ subjects in all levels', 'MFO: Research ServicesExtension Services Advance Education ServicesHigher Education Services', 'Preparation of syllabi and classroom teaching integrating gender perspective', 'Number of faculty members integrated Gender perspective in the syllabus - 567 permanent and 125 COS females and males faculty integrating and development of gender-sensitive learning materials', 58295539.71, 'PS 567 Teaching employees and COS 51,294,972.71 Teaching Overload 7,000,000.00', 'GAA GAA', 'GAD Office, GFPS-TWG members, all colleges', 'organization-focused activity'),
(27, 'Need to sustain a functional and gender-responsive GAD Focal Point System (GFPS) and GAD Office to ensure the effective mainstreaming of gender perspective in BSU academic, research, extension, and administrative programs/Section 37-C2 of the Magna Carta ', 'Sustained operations of the existing GAD Office-Provision of administrative, logistical, and financial support for the day-to-day functioning of the GAD Office maintenance of GAD database and documentation systems coordination of GFPS and GAD-related activities across colleges and units.', 'To ensure the continuous and efficient operation of a functional, gender-responsive GAD Office that leads, monitors, and evaluates GAD mainstreaming efforts in the university.', 'MFO: Research ServicesExtension Services Advance Education ServicesHigher Education Services', 'Sustaining Gender Mainstreaming and Institutional Support in the University', 'Fully maintained GAD Office - 100% fully maintained GAD office', 8052420.09, 'PS on Procurement Process 30,992.09 PS Attribution: Execom & TWG members 7,219,424.00 Supplies Equipment and Materials 300,000.00 PS of GAD Director(50%) 501,954.00', 'GAA GAA GAA GAA', 'GAD Office', 'organization-focused activity'),
(28, '', '', '', '', 'Transportation Equipment Outlay', '', 12285000.00, '', 'GAA', 'TASU, PMO, SPMO', 'attributed program'),
(29, '', '', '', '', 'Repair and Maintenance Office Building and other Structures', '', 6402000.00, '', 'GAA', 'PU, PMO, SPMO', 'attributed program'),
(30, '', '', '', '', 'Bamboo Industry Development for Environment Conservation and Countryside', '', 3750000.00, '', 'GAA', 'PU, PMO, SPMO', 'attributed program'),
(31, '', '', '', '', 'Benguet State University Student Information and Accounting System (SIAS)', '', 5000000.00, '', 'GAA', 'ICT, PMO', 'attributed program');

-- --------------------------------------------------------

--
-- Table structure for table `office_units`
--

CREATE TABLE `office_units` (
  `office_id` int(11) NOT NULL,
  `unit_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `office_units`
--

INSERT INTO `office_units` (`office_id`, `unit_name`) VALUES
(5, 'College of Arts and Sciences'),
(6, 'College of Business Administration'),
(8, 'College of Education'),
(9, 'College of Engineering'),
(11, 'College of Information Sciences'),
(10, 'College of Nursing'),
(1, 'GAD Office'),
(12, 'Graduate School'),
(13, 'Human Resource Management Office'),
(2, 'Office of the President'),
(3, 'Office of the Vice President for Academic Affairs'),
(4, 'Office of the Vice President for Administration and Finance'),
(14, 'Registrar\'s Office'),
(15, 'Research and Development Office'),
(16, 'Student Affairs Office');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `log_id` bigint(20) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action_type` varchar(100) DEFAULT NULL,
  `target_table` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `log_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `remember_token`, `created_at`) VALUES
(1, 'bisayotduligas@gmail.com', '$2y$10$SsLnNlFpnvIvYzbQSZtaE.E62WfcA4Q9N5blPdexrYvetCNLD.8Ju', NULL, '2026-05-27 02:58:36'),
(2, 'gadstaff@bsu.edu.ph', '$2y$10$UHCJGVqGfN7oNJltZhBwJeIasLyp0K45h4SMbJT8khK4A3lZrLHla', NULL, '2026-05-28 02:09:35');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `user_role` enum('Director','Staff','TWG','Non-TWG') DEFAULT NULL,
  `office_unit_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`user_id`, `first_name`, `middle_name`, `last_name`, `user_role`, `office_unit_id`) VALUES
(1, 'Joshua', NULL, 'Duligas', 'Staff', NULL),
(2, 'gad', NULL, 'staff', 'Staff', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accomplishment_report`
--
ALTER TABLE `accomplishment_report`
  ADD PRIMARY KEY (`ar_id`),
  ADD KEY `control_number` (`control_number`),
  ADD KEY `ar_status_ibfk` (`status_id`);

--
-- Indexes for table `activity_design`
--
ALTER TABLE `activity_design`
  ADD PRIMARY KEY (`ad_id`),
  ADD UNIQUE KEY `uq_control_number` (`control_number`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `activity_statuses`
--
ALTER TABLE `activity_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Indexes for table `gad_plan_budget`
--
ALTER TABLE `gad_plan_budget`
  ADD PRIMARY KEY (`gpb_id`);

--
-- Indexes for table `office_units`
--
ALTER TABLE `office_units`
  ADD PRIMARY KEY (`office_id`),
  ADD UNIQUE KEY `unit_name` (`unit_name`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `office_unit_id` (`office_unit_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accomplishment_report`
--
ALTER TABLE `accomplishment_report`
  MODIFY `ar_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_design`
--
ALTER TABLE `activity_design`
  MODIFY `ad_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `activity_statuses`
--
ALTER TABLE `activity_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `gad_plan_budget`
--
ALTER TABLE `gad_plan_budget`
  MODIFY `gpb_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `office_units`
--
ALTER TABLE `office_units`
  MODIFY `office_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accomplishment_report`
--
ALTER TABLE `accomplishment_report`
  ADD CONSTRAINT `accomplishment_report_ibfk_1` FOREIGN KEY (`control_number`) REFERENCES `activity_design` (`control_number`) ON DELETE CASCADE,
  ADD CONSTRAINT `ar_status_ibfk` FOREIGN KEY (`status_id`) REFERENCES `activity_statuses` (`id`);

--
-- Constraints for table `activity_design`
--
ALTER TABLE `activity_design`
  ADD CONSTRAINT `activity_design_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `activity_statuses` (`id`),
  ADD CONSTRAINT `activity_design_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_profiles_ibfk_2` FOREIGN KEY (`office_unit_id`) REFERENCES `office_units` (`office_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

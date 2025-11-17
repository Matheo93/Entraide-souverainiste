-- =========================================
-- ADMIN QUERIES - INVISIBLE USER BEHAVIOR
-- Pour voir les stats sans interface admin
-- =========================================

-- 1. PROFITEURS - Utilisateurs avec score >= 60
SELECT
    u.id,
    u.email,
    s.profiteur_level as 'Niveau',
    s.profiteur_score as 'Score Profiteur',
    s.ratio_offres_demandes as 'Ratio O/D',
    s.points_offres as 'Points Offres',
    s.points_demandes as 'Points Demandes',
    s.total_offres as 'Total Offres',
    s.total_demandes as 'Total Demandes',
    s.taux_abandon as 'Taux Abandon',
    s.discussions_total as 'Discussions',
    s.last_calculated_at as 'Dernière MàJ'
FROM user_behavior_stats s
JOIN user u ON s.user_id = u.id
WHERE s.profiteur_level = 'PROFITEUR'
ORDER BY s.profiteur_score DESC;

-- 2. SUSPECTS - Utilisateurs avec score entre 40 et 60
SELECT
    u.id,
    u.email,
    s.profiteur_level as 'Niveau',
    s.profiteur_score as 'Score Profiteur',
    s.ratio_offres_demandes as 'Ratio O/D',
    s.points_offres as 'Points Offres',
    s.points_demandes as 'Points Demandes',
    s.total_offres as 'Total Offres',
    s.total_demandes as 'Total Demandes',
    s.taux_abandon as 'Taux Abandon'
FROM user_behavior_stats s
JOIN user u ON s.user_id = u.id
WHERE s.profiteur_level = 'SUSPECT'
ORDER BY s.profiteur_score DESC;

-- 3. TOP 10 GÉNÉREUX - Meilleurs ratios offres/demandes
SELECT
    u.id,
    u.email,
    s.profiteur_level as 'Niveau',
    s.ratio_offres_demandes as 'Ratio O/D',
    s.total_offres as 'Total Offres',
    s.total_demandes as 'Total Demandes',
    s.points_offres as 'Points Offres',
    s.points_demandes as 'Points Demandes',
    ROUND(s.points_offres / NULLIF(s.points_demandes, 0), 2) as 'Ratio Points'
FROM user_behavior_stats s
JOIN user u ON s.user_id = u.id
WHERE s.total_demandes > 0
ORDER BY s.ratio_offres_demandes DESC
LIMIT 10;

-- 4. UTILISATEURS AVEC FORT TAUX D'ABANDON
SELECT
    u.id,
    u.email,
    s.taux_abandon as 'Taux Abandon',
    s.discussions_total as 'Total Discussions',
    s.discussions_abandonnees as 'Abandonnées',
    s.discussions_avec_accord as 'Avec Accord',
    s.profiteur_score as 'Score'
FROM user_behavior_stats s
JOIN user u ON s.user_id = u.id
WHERE s.taux_abandon > 0.3
  AND s.discussions_total >= 3
ORDER BY s.taux_abandon DESC;

-- 5. HISTORIQUE POINTS PAR UTILISATEUR
SELECT
    u.email,
    pt.transaction_type as 'Type',
    pt.points_change as 'Changement',
    pt.balance_after as 'Solde Après',
    pt.details as 'Détails',
    pt.created_at as 'Date',
    c.id as 'Conv ID'
FROM points_transactions pt
JOIN user u ON pt.user_id = u.id
LEFT JOIN conversations c ON pt.related_conversation_id = c.id
WHERE u.email = 'email@exemple.com'
ORDER BY pt.created_at DESC;

-- 6. VUE D'ENSEMBLE - Tous les utilisateurs avec stats
SELECT
    u.id,
    u.email,
    s.profiteur_level as 'Niveau',
    s.profiteur_score as 'Score',
    s.ratio_offres_demandes as 'Ratio O/D',
    CONCAT(s.points_offres, '/', s.points_demandes) as 'Points O/D',
    CONCAT(s.total_offres, '/', s.total_demandes) as 'Total O/D',
    s.discussions_total as 'Discussions',
    s.taux_abandon as 'Taux Abandon'
FROM user u
LEFT JOIN user_behavior_stats s ON s.user_id = u.id
ORDER BY s.profiteur_score DESC;

-- 7. LIMITATIONS ACTIVES
SELECT
    u.email,
    l.limitation_type as 'Type Limitation',
    l.reason as 'Raison',
    l.applied_at as 'Appliquée le',
    l.expires_at as 'Expire le',
    l.is_active as 'Active'
FROM user_limitations l
JOIN user u ON l.user_id = u.id
WHERE l.is_active = 1;

-- 8. IP BANNIES
SELECT
    ip_address as 'IP',
    reason as 'Raison',
    banned_at as 'Banni le',
    expires_at as 'Expire le',
    is_active as 'Actif',
    related_announce_id as 'Annonce ID',
    u_banned.email as 'Utilisateur banni',
    u_admin.email as 'Admin'
FROM ip_bans
LEFT JOIN user u_banned ON banned_user_id = u_banned.id
LEFT JOIN user u_admin ON banned_by_admin_id = u_admin.id
WHERE is_active = 1;

-- 9. STATISTIQUES GLOBALES
SELECT
    COUNT(*) as 'Total Utilisateurs avec Stats',
    SUM(CASE WHEN profiteur_level = 'PROFITEUR' THEN 1 ELSE 0 END) as 'Profiteurs',
    SUM(CASE WHEN profiteur_level = 'SUSPECT' THEN 1 ELSE 0 END) as 'Suspects',
    SUM(CASE WHEN profiteur_level = 'NORMAL' THEN 1 ELSE 0 END) as 'Normaux',
    AVG(ratio_offres_demandes) as 'Ratio Moyen',
    AVG(profiteur_score) as 'Score Moyen',
    AVG(taux_abandon) as 'Taux Abandon Moyen'
FROM user_behavior_stats;

-- 10. CONVERSATIONS PAR STATUT
SELECT
    status as 'Statut',
    COUNT(*) as 'Nombre',
    AVG(messages_count) as 'Messages Moyen',
    AVG(TIMESTAMPDIFF(HOUR, created_at,
        IFNULL(closed_at, NOW()))) as 'Durée Moyenne (heures)'
FROM conversations
GROUP BY status
ORDER BY COUNT(*) DESC;

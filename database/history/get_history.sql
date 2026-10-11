SELECT * FROM record_history
WHERE subdomain_id IN (:ids)
ORDER BY detected_at DESC, id DESC
LIMIT ?
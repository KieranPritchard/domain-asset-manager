SELECT * FROM record_history
WHERE subdomain_id IN (" . implode(",", array_fill(0, count($subdomain_ids), "?")) . ")
ORDER BY detected_at DESC, id DESC
LIMIT ?
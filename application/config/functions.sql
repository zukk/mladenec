-------------- GISWithin

DELIMITER // 

CREATE FUNCTION GISWithin(pt POINT, mp MULTIPOLYGON) RETURNS INT(1) DETERMINISTIC 
BEGIN 

DECLARE str, xy TEXT; 
DECLARE x, y, p1x, p1y, p2x, p2y, m, xinters DECIMAL(16, 13) DEFAULT 0; 
DECLARE counter INT DEFAULT 0; 
DECLARE p, pb, pe INT DEFAULT 0; 

SELECT MBRWithin(pt, mp) INTO p; 
IF p != 1 OR ISNULL(p) THEN 
RETURN p; 
END IF; 

SELECT X(pt), Y(pt), ASTEXT(mp) INTO x, y, str; 
SET str = REPLACE(str, 'POLYGON((',''); 
SET str = REPLACE(str, '))', ''); 
SET str = CONCAT(str, ','); 

SET pb = 1; 
SET pe = LOCATE(',', str); 
SET xy = SUBSTRING(str, pb, pe - pb); 
SET p = INSTR(xy, ' '); 
SET p1x = SUBSTRING(xy, 1, p - 1); 
SET p1y = SUBSTRING(xy, p + 1); 
SET str = CONCAT(str, xy, ','); 

WHILE pe > 0 DO 
SET xy = SUBSTRING(str, pb, pe - pb); 
SET p = INSTR(xy, ' '); 
SET p2x = SUBSTRING(xy, 1, p - 1); 
SET p2y = SUBSTRING(xy, p + 1); 
IF p1y < p2y THEN SET m = p1y; ELSE SET m = p2y; END IF; 
IF y > m THEN 
IF p1y > p2y THEN SET m = p1y; ELSE SET m = p2y; END IF; 
IF y <= m THEN 
IF p1x > p2x THEN SET m = p1x; ELSE SET m = p2x; END IF; 
IF x <= m THEN 
IF p1y != p2y THEN 
SET xinters = (y - p1y) * (p2x - p1x) / (p2y - p1y) + p1x; 
END IF; 
IF p1x = p2x OR x <= xinters THEN 
SET counter = counter + 1; 
END IF; 
END IF; 
END IF; 
END IF; 
SET p1x = p2x; 
SET p1y = p2y; 
SET pb = pe + 1; 
SET pe = LOCATE(',', str, pb); 
END WHILE; 

RETURN counter % 2; 

END; 

DELIMITER ; 

-------------- \ GISWithin

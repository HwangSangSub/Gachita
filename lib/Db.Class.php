<?

class DBPDO
{
    private $db;
     
    function __construct($DB_con)
    {
        $this->db = $DB_con;
    }
     

	//sum값 구하기
	public function GetSum($cols, $table, $where='', $value, $like=false) {

        $query = "SELECT  SUM({$cols}) FROM `{$table}` WHERE 1 ";

		if($like){
			$query .= "`{$key}` LIKE '%{$value}%' {$operand} ";
		}else if ($where) {
			$query .= " AND `{$where}` = ?  ";
		}

        $stmt = $this->db->prepare($query);
		$stmt->bindValue(1, $value);

		try { 
			$stmt->execute();     
			$rows =  $stmt->fetch();
			return $rows[0];

		} catch (PDOException $e){die($e->getMessage());}  
	} 


	//max값 구하기
	public function GetMax($cols, $table, $where='', $value, $like=false) {

        $query = "SELECT  MAX({$cols}) FROM `{$table}` WHERE 1 ";

		if($like){
			$query .= "`{$key}` LIKE '%{$value}%' {$operand} ";
		}else if ($where) {
			$query .= " AND `{$where}` = ?  ";
		}

        $stmt = $this->db->prepare($query);
		$query->bindValue(1, $value);

		try { 
			$stmt->execute();     
			$rows =  $stmt->fetch();
			return $rows[0];

		} catch (PDOException $e){die($e->getMessage());}  
	} 


	//count 구하기
	public function GetCount($cols, $table, $where='', $value, $like=false) {

        $query = "SELECT  COUNT({$cols}) FROM `{$table}` WHERE 1 ";

		if($like){
			$query .= "`{$key}` LIKE '%{$value}%' {$operand} ";
		}else if ($where) {
			$query .= " AND {$where}  ";
		}


        $stmt = $this->db->prepare($query);
		$stmt->bindValue(1, $value);

		try { 
			$stmt->execute();     
			$rows =  $stmt->fetch();
			return $rows[0];

		} catch (PDOException $e){die($e->getMessage());}  
	} 


	// 생성후 30분 지나면  날짜 취소 처리 
    public function chkDayList()  {

        $stmt = $this->db->prepare("SELECT idx, taxi_SDate FROM TB_STAXISHARING WHERE taxi_State = '1'");
		try { 
			$stmt->execute();     
			$count = $stmt->rowCount();
			if($count < 1)  { //아닐경우
			} else {

				while($row=$stmt->fetch(PDO::FETCH_ASSOC)) {

					$schkIdx = trim($row['idx']);	      // 고유번호
					$schkSDate = trim($row['taxi_SDate']);				  // 택시 출발일시간

					$chkNDate = date($schkSDate, strtotime('+30 minutes'));  //생성후 30분

					if ( DU_TIME_YMDHIS > $chkNDate ) {
						$upDStmt = $this->db->prepare("UPDATE TB_STAXISHARING SET taxi_State = '7' WHERE idx = :schkIdx LIMIT 1");
						$upDStmt->bindparam(":schkIdx",$schkIdx);
						$upDStmt->execute();
					}

				}
			}

		} catch (PDOException $e){die($e->getMessage());}  

	}

   public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM tbl_users WHERE id=:id");
        $stmt->bindparam(":id",$id);
        $stmt->execute();
        return true;
    }
     
    

}


?>
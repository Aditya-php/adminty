<?php 
/*
@author : Aditya
@param  : none
@desc   : It's used to generate the unique user id
@return int(user id)
*/
if(!function_exists('generateUserId'))
{
	function generateUserId()
	{
		$obj=& get_instance();
		$encypt1=uniqid(rand(100000,999999), true);
		$usid1=str_replace(".", "", $encypt1);
		$pre_userid = substr($usid1, 0, 7);
		$query=$obj->db->select('user_id')->from('user_registration')->where(array('user_id'=>$pre_userid))->get();
		if($query->num_rows()>0)
		{
		 generateUserId();
		}
		else
		{
		 return $pre_userid;
	    }
	}//end function    
}//end function exists
/*
In Binary Like Matrix if selected leg position is auto and it's link to getMatrixNom
*/
function getLegPosition1($sponserid)
{
			global $nom_id1,$lev,$leg_pos;
			$obj=& get_instance();
			foreach($sponserid as $key => $val)
			{
			$query1=$obj->db->select('*')->from('user_registration')->where('nom_id',$val)->order_by('id','ASC')->get();
			$num_ro1[]=$query1->num_rows();
			//$num_ro1[]=mysql_num_rows($result1);
			foreach($query1->result() as $row)
				{
					$rclid1[]=$row->user_id;
				}//end while
			}//end foreach
			foreach($num_ro1 as $key11 => $valu)
			{
				if($valu < 2)
				{
				$key1=$key11;
				break;
				}
			}//end foreach
			switch ($valu)
			{
			    case '0':
				    $leg_pos="left";
					break;
			    case '1':
				   	$leg_pos="right";
					break;
				case '2':
					if(!empty($nom_id1))
					{
					 break;
					}
			    getLegPosition1($rclid1);
			}//end switch
			return $leg_pos;
}//end function
/*
In Binary Like Matrix if selected leg position is auto and it's link to getLegPosition1
*/
if(!function_exists('getMatrixNom'))
{
	function getMatrixNom($sponserid)
	{
			global $nom_id1,$lev;
			$obj=& get_instance();
			foreach($sponserid as $key => $val)
			{
			//$query1="select * from user_registration where nom_id='$val' order by id asc";
			//$result1=mysql_query($query1);
			$query1=$obj->db->select('*')->from('user_registration')->where('nom_id',$val)->order_by('id','ASC')->get();
			$num_ro1[]=$query1->num_rows();
			//$num_ro1[]=mysql_num_rows($result1);
			foreach($query1->result() as $row)
				{
					$rclid1[]=$row->user_id;
				}//end while
			}//end foreach
			foreach($num_ro1 as $key11 => $valu)
			{
				if($valu < 2)
				{
				$key1=$key11;
				break;
				}
			}//end foreach
			switch ($valu)
			{
			    case '0':
				    $nom_id1=$sponserid[$key1];
					break;
			    case '1':
				   	$nom_id1=$sponserid[$key1];
					break;
				case '2':
					if(!empty($nom_id1))
					{
					 break;
					}
			    getMatrixNom($rclid1);
			}//end switch
			return $nom_id1;
	}//end function
}//end function exists
/*
@author : Aditya
@param  : int(referral userid/sponsor user id)
@desc   : It's used to identify the weaker leg position, in case of default leg user registration system
@return string(leg position)
*/
if(!function_exists('getNom'))
{
	function getNom($sponserid=null,$posi=null)
	{
		$nom_id=null;
		$obj=& get_instance();
	    $query=$obj->db->select('*')->from('user_registration')->where(array('nom_id'=>$sponserid,'binary_pos'=>$posi))->get();
        if($query->num_rows()>0)
        {
	        $query_obj=$query->row();
		    $rclid1=$query_obj->user_id;
		    $posi=$query_obj->binary_pos;
			if($rclid1!="")
			{
			   return getNom($rclid1,$posi);

			}
			else 
			{
			    
			    $nom_id=$sponserid;	
			} 
        }
		else 
		{
			$nom_id=$sponserid;
		}
		//echo $nom_id;
		return $nom_id;
	}//end function
}//end function exists

/*function to show user on which level code ends here*/
if(!function_exists('level_countdd'))
{
	function level_countdd($user_id,$income_id)
	{
		$level=null;
		$obj=& get_instance();
		$query_obj=$obj->db->select('*')->from('user_registration')->where('user_id',$user_id)->get()->row();
		$nom_id=$query_obj->nom_id;
		$level=1;
		if($nom_id!=$income_id)
		{
			level_countdd($nom_id,$income_id);
			$level++;
		}
		else
		{
			$level=1;
		}
		return $level;
	}//end function
}//end function exists
/*function to show user on which level code ends here*/
/*
@author : Aditya
@param  : int(referral userid/sponsor user id), int($pkg_id)
@desc   : It's used to update the sponser rank and provide bonus for updated rank all the upliner rank
@return int(nom_id)
*/
if(!function_exists('updateRank'))
{
	function updateRank()
	{
      $obj=& get_instance();
      $query=$obj->db->select('*')->from('user_registration')->order_by('id')->get();
      foreach ($query->result() as $userObj) 
      {
      	$all_team_query=$obj->db->select('id')->from('level_income_binary')->where(array('income_id ='=>$userObj->user_id))->get();
      	$all_ref_query=$obj->db->select('user_id')->from('user_registration')->where(array('ref_id ='=>$userObj->user_id))->get();
      	$total_team_member=$all_team_query->num_rows();
      	$total_direct_member=$all_ref_query->num_rows();
      	$rank_obj=getRankDetails($total_direct_member,$total_team_member);
	    if(!empty($rank_obj) && $rank_obj!=null)
	      	{
			    if($userObj->rank_id!=$rank_obj->id)
			    {
			          ///////
			      	   $user_id=$userObj->user_id;
			      	   $bonus_amount=$rank_obj->bonus_amount;
			      	   $rank_name=$rank_obj->rank_name;
			           $obj->db->update('user_registration',array('rank_id'=>$rank_obj->id,'rank_name'=>$rank_name),array('user_id'=>$user_id));
			           //@Desc:It's used to manage rank log
			           //////////
			           $query_obj=$obj->db->select('amount')->from('final_e_wallet')->where('user_id',$user_id)->get()->row();
				       
				       $balance=$query_obj->amount+$bonus_amount;
				       $obj->db->update('final_e_wallet',array('amount'=>$balance),array('user_id'=>$user_id));


					   ///////////
					   //'1'=>debit for pkg purchased, '2'=> debit for ewallet withdrawl, '3'=>debit for balance transfer, '4'=>'credit for balance transfer received', '5'=>credit for direct commission, '6'=>credit for binary commission, '7'=>credit for matching commission, '9'=>credit for unilevel commission, '10'=>credit for rank bonus update
					   /*
			           Note: status field '0'=>debit,'1'=>credit
			           */
			           $transaction_no=generateUniqueTranNo();

					   $obj->db->insert('credit_debit',array(
						    'transaction_no'=>$transaction_no,
						    'user_id'=>$user_id,
						    'credit_amt'=>$bonus_amount,
						    'debit_amt'=>'0',
						    'balance'=>$balance,
						    'admin_charge'=>'0',
						    'receiver_id'=>$user_id,
						    'sender_id'=>COMP_USER_ID,
						    'receive_date'=>date('d-m-Y'),
						    'ttype'=>'Rank bonus amount',
						    'TranDescription'=>'bonus amount for update rank '.$rank_name,
						    'Cause'=>'bonus amount for update rank '.$rank_name,
						    'Remark'=>'bonus amount for update rank '.$rank_name,
						    'invoice_no'=>'',
						    'product_name'=>'',
						    'status'=>'1',
						    'ewallet_used_by'=>'',
						    'current_url'=>ci_site_url(),
						    'reason'=>'10'
					        ));

					 	$obj->db->insert('rank_log',array(
			           	'user_id'=>$user_id,
			           	'rank_id'=>$rank_obj->id,
			           	'rank_name'=>$rank_name,
			           	'transaction_no'=>$transaction_no
			           	));
  
			    }//end if          		
      	    }//end empty if here!
      }//end foreach	
   }//end function
}//end function exists
/*
@author : Aditya
@param  : int(direct_members), int(team_members)
@desc   : It's used to get the rank details for any user on the basis on total direct_members and total team_members
@return : assoc array
*/

if(!function_exists('getRankDetails'))
{
	function getRankDetails($direct_members=null,$team_members=null)
	{
	$obj=& get_instance();
	$data=array();
	$match=null;
	$total_members=$direct_members+$team_members;
	//$rank_query=mysql_query("select *,(direct_member+team_member) as total_members from rank as r order by total_members desc") or die(mysql_error());
    $rank_query=$obj->db->query("select *,(direct_member+team_member) as total_members from rank as r order by total_members desc");
	$total_member=array();
	if($rank_query->num_rows()>0)
	{
			foreach($rank_query->result() as $objs)
			{
			  $total_member[]=$objs;
			}
    }//end if
	if($rank_query->num_rows()>0)
	{
		for($i=0;$i<count($total_member);$i++)
		{
			$total=$total_member[$i]->total_members;
			if($total_members==$total)
			{
				if($team_members>=$total_member[$i]->team_member and $direct_members>=$total_member[$i]->direct_member)
				{
				$match=$total_member[$i];
				break;
				}
			}
			else if($total_members>$total)
			{
				if($team_members>=$total_member[$i]->team_member and $direct_members>=$total_member[$i]->direct_member)
				{
			    $match=$total_member[$i];
				break;
				}    
			}
		}
	}//end if
	$data=$match;
	return $data;
    }//end function
}//end function exists

/*
@author : Aditya
@param  : int(sponser_id), int(pkg_id), int(user_id)
@desc   : It's used to credit the direct commission to sponser on behalf of pkg_id,rank_id
@return none
*/
if(!function_exists('creditDirectCommission'))
{
	function creditDirectCommission($sponser_id=null,$pkg_id=null,$user_id=null,$pkg_name=null,$pkg_amount)
	{
	   $obj=& get_instance();
	   $query=$obj->db->query("select d.commission as commission, d.type as typ, p.amount as pkg_amount from package as p 
	     inner join direct_commission as d on d.pkg_id=p.id where p.id='$pkg_id'");
	   if($query->num_rows()>0)
	   {
		     $commission_obj=$query->row();
		     /*
		     Note:typ==1 for percent and typ==2 for flat
		     */
		     if($commission_obj->typ==1)
		     { 
		     	$commission_amount=($pkg_amount*$commission_obj->commission)/100;
		     }
		     else if($commission_obj->typ==2)
		     {
	            $commission_amount=$commission_obj->commission;
		     }
			 if(is_active_secondry_ewallet())
			 {
				$commission_amount1=$commission_amount;
				$deduction_percent=get_secondry_wallet_deduction();
				$secondry_wallet_commission=($commission_amount*$deduction_percent)/100;
				$commission_amount=$commission_amount-$secondry_wallet_commission;
				
				$query_obj=$obj->db->select('amount')->from('secondry_e_wallet')->where('user_id',$sponser_id)->get()->row();
			    $balance=$query_obj->amount+$secondry_wallet_commission;
			    $obj->db->update('secondry_e_wallet',array('amount'=>$balance),array('user_id'=>$sponser_id));
				
				$obj->db->insert('secondry_wallet_credit_debit',array(
			    'transaction_no'=>generateUniqueTranNo(),
			    'user_id'=>$sponser_id,
			    'credit_amt'=>$secondry_wallet_commission,
			    'debit_amt'=>'0',
			    'balance'=>$balance,
			    'admin_charge'=>'0',
			    'receiver_id'=>$sponser_id,
			    'sender_id'=>$user_id,
			    'receive_date'=>date('d-m-Y'),
			    'ttype'=>$deduction_percent.'%  of Direct Commission Amount '.$commission_amount1,
			    'TranDescription'=>$deduction_percent.'%  of Direct Commission Amount '.$commission_amount1,
			    'Cause'=>$pkg_name.' Package Purchase by '.$user_id,
			    'Remark'=>$pkg_name.' Package Purchase by '.$user_id,
			    'invoice_no'=>'',
			    'product_name'=>'',
			    'status'=>'1',
			    'ewallet_used_by'=>'Withdrawal Wallet',
			    'current_url'=>ci_site_url(),
			    'reason'=>'5',
				'pkg_id'=>$pkg_id
		        ));
			 }
			 $query_obj=$obj->db->select('amount')->from('final_e_wallet')->where('user_id',$sponser_id)->get()->row();
			 $balance=$query_obj->amount+$commission_amount;
			 $obj->db->update('final_e_wallet',array('amount'=>$balance),array('user_id'=>$sponser_id));
			//'1'=>debit for pkg purchased, '2'=> debit for ewallet withdrawl, '3'=>debit for balance transfer, '4'=>'credit for balance transfer received', '5'=>credit for direct commission, '6'=>credit for binary commission, '7'=>credit for matching commission, '9'=>credit for unilevel commission, '10'=>credit for rank bonus update
			/*
			Note: status field '0'=>debit,'1'=>credit
			*/
			$obj->db->insert('credit_debit',array(
			    'transaction_no'=>generateUniqueTranNo(),
			    'user_id'=>$sponser_id,
			    'credit_amt'=>$commission_amount,
			    'debit_amt'=>'0',
			    'balance'=>$balance,
			    'admin_charge'=>'0',
			    'receiver_id'=>$sponser_id,
			    'sender_id'=>$user_id,
			    'receive_date'=>date('d-m-Y'),
			    'ttype'=>'Direct Commission Amount',
			    'TranDescription'=>$pkg_name.' Package Purchase by '.$user_id,
			    'Cause'=>$pkg_name.' Package Purchase by '.$user_id,
			    'Remark'=>$pkg_name.' Package Purchase by '.$user_id,
			    'invoice_no'=>'',
			    'product_name'=>'',
			    'status'=>'1',
			    'ewallet_used_by'=>'Withdrawal Wallet',
			    'current_url'=>ci_site_url(),
			    'reason'=>'5',
				'pkg_id'=>$pkg_id
		        ));
	   }//end if
	}//end function
}//end function exists
/*
@author : Aditya
@param  : int(pkg_id), int(user_id), int(level), int(pkg_amount)
@desc   : It's used to get the the Unilevel commission
@return : int(commission amount)
*/
function getUnilevelCommission($pkg_id=null,$user_id=null,$level=null,$pkg_amount=null)
{
	$obj=& get_instance();
	/*
	->note: if level_type==1 then limited and if level_type==0 unlimited
	->note: if commission_type==1 the percent type commission and if commission_type==2 then flat type commission 
	*/
	$commission_amount=0;

	$level_type_query=$obj->db->query("SELECT uni.level_type as level_type FROM unilevel_commission as uni where uni.pkg_id='$pkg_id'");
	

	//echo $obj->db->last_query()."<br>";


   
	if($level_type_query->num_rows()>0)
	{
	  $level_type=$level_type_query->row();
	  /*
	    for Unlimited level
	  */
	  if($level_type->level_type==0)
	  {
		 
		 $query=$obj->db->query("SELECT uni.commission_type as com_type, uni.commission as commission, uni.level_type FROM unilevel_commission as uni where uni.pkg_id='$pkg_id'");
						   
	     


	     if($query->num_rows()>0)
		 {
			 $query_result=$query->row();
			 /*
			 percent commission
			 */
			 if($query_result->com_type==1)
			 {
				 $commission_amount=($pkg_amount*$query_result->commission)/100; 
			 }
			 /*
			 flat commission
			 */
			if($query_result->com_type==2)
			{
				$commission_amount=$query_result->commission; 
			}
		 }//end num_rows if here!
	  }
	  /*
	  for limited level
	  */
	  else if($level_type->level_type==1)
	  {
		   $query=$obj->db->query("SELECT uni.commission_type as com_type, unimeta.level_commission as commission FROM unilevel_commission as uni 
			join unilevel_commission_meta as unimeta on uni.id=unimeta.unilevel_commission_id and unimeta.level='$level'
			where uni.pkg_id='$pkg_id'");

			if($query->num_rows()>0)
			{
			   $query_result=$query->row();
			   /*
			   percent commission
			   */
			   if($query_result->com_type==1)
			   {
				 $commission_amount=($pkg_amount*$query_result->commission)/100;
			   }
			   /*
			   flat commission
			   */
			   else if($query_result->com_type==2)
			   {
				   $commission_amount=$query_result->commission;
			   }				
			}//end num_rows if here!
	  }//end level type else if here
	}//end if 
    $commission_amount;
	return $commission_amount;
	exit;
}//end function
/*
@author : Aditya
@param  : int(pkg_id),int(user_id),int(pkg_amount)
@desc   : It's used to credit the Unilevel commission
@return none
*/
function creditUnilevelCommission($pkg_id=null,$user_id=null,$pkg_amount=null,$pkg_name=null)
{
	$obj=& get_instance();
	
	$all_upliners=$obj->db->select('*')->from('direct_matrix_downline')->where('down_id',$user_id)->get()->result();
	
	foreach($all_upliners as $upliner)
	{
		$is_ref=$obj->db->select('*')->from('user_registration')->where(array('user_id'=>$user_id,'ref_id'=>$upliner->income_id))->get()->num_rows();
		
		
		
		/////////////////
		$user_ids=$upliner->income_id;
		
		//$upliner_level_info=$obj->db->select('level')->from('matrix_downline')->where(array('down_id'=>$user_id,'income_id'=>$user_ids))->get()->row();
		
		$level=$upliner->level;
		
		///////////////////////////////////
		$user_details=get_user_details($user_ids);
		
		$pkg_details=get_package_details($user_details->pkg_id);
		
		$commission_amount=getUnilevelCommission($user_details->pkg_id,$user_ids,$level,$pkg_amount);
		
		///////////////////////////////////
		if($commission_amount>0 && $is_ref<=0)
		{
			$commision_amount1=$commission_amount;
			
			if(is_active_secondry_ewallet())
			{
				$deduction_percent=get_secondry_wallet_deduction();
				$secondry_wallet_commission=($commission_amount*$deduction_percent)/100;
				$commission_amount=$commission_amount-$secondry_wallet_commission;
				////////////////////////////////////////////////////////////////////
				$query_obj=$obj->db->select('amount')->from('secondry_e_wallet')->where('user_id',$user_ids)->get()->row();
				$balance=$query_obj->amount+$secondry_wallet_commission;
				$obj->db->update('secondry_e_wallet',array('amount'=>$balance),array('user_id'=>$user_ids));
				//
				$obj->db->insert('secondry_wallet_credit_debit',array(
					'transaction_no'=>generateUniqueTranNo(),
					'user_id'=>$user_ids,
					'credit_amt'=>$secondry_wallet_commission,
					'debit_amt'=>'0',
					'balance'=>$balance,
					'admin_charge'=>'0',
					'receiver_id'=>$user_ids,
					'sender_id'=>$user_id,
					'receive_date'=>date('d-m-Y'),
					'ttype'=>$deduction_percent.'% of Unilevel Income of package '.$pkg_name." amount".$commision_amount1,
					'TranDescription'=>$deduction_percent.'% of Unilevel Income'.$commision_amount1,
					'Cause'=>'Commission of Unilevel Income of package'.$pkg_name,
					'Remark'=>'Unilevel Income of package '.$pkg_name,
					'invoice_no'=>'',
					'product_name'=>'',
					'status'=>'1',
					'ewallet_used_by'=>'',
					'current_url'=>ci_site_url(),
					'reason'=>'9',
					'level'=>$level,
					'pkg_id'=>$pkg_id
					));		
			}
			$query_obj=$obj->db->select('amount')->from('final_e_wallet')->where('user_id',$user_ids)->get()->row();
			$balance=$query_obj->amount+$commission_amount;
			$obj->db->update('final_e_wallet',array('amount'=>$balance),array('user_id'=>$user_ids));
			//reason enum filed '1'=>debit for pkg purchased, '2'=> debit for ewallet withdrawl, '3'=>debit for balance transfer, '4'=>'credit for balance transfer received', '5'=>credit for direct commission, '6'=>credit for binary commission, '7'=>credit for matching commission, '9'=>credit for unilevel commission, '10'=>credit for rank bonus update
			/*
			Note: status field '0'=>debit,'1'=>credit
			*/
			$obj->db->insert('credit_debit',array(
							'transaction_no'=>generateUniqueTranNo(),
							'user_id'=>$user_ids,
							'credit_amt'=>$commission_amount,
							'debit_amt'=>'0',
							'balance'=>$balance,
							'admin_charge'=>'0',
							'receiver_id'=>$user_ids,
							'sender_id'=>$user_id,
							'receive_date'=>date('d-m-Y'),
							'ttype'=>'Unilevel Income of package '.$pkg_name,
							'TranDescription'=>'Earn Unilevel Income of package '.$pkg_name,
							'Cause'=>'Commission of Unilevel Income of package'.$pkg_name,
							'Remark'=>'Unilevel Income of package '.$pkg_name,
							'invoice_no'=>'',
							'product_name'=>'',
							'status'=>'1',
							'ewallet_used_by'=>'',
							'current_url'=>ci_site_url(),
							'reason'=>'9',
							'level'=>$level,
							'pkg_id'=>$pkg_id
							));		
			}//end commision_amount>0 if here	
	}
}
function get_knowledege_points($pkg_id)
{
	$obj=& get_instance();
	//$user_details=get_user_details($income_id);
	//$pkg_id=$user_details->pkg_id;
	$pkg_info=$obj->db->select('knowledge_points')->from('package')->where(array('id'=>$pkg_id))->get()->row();
	return $pkg_info->knowledge_points;
}
////////////
/*
@author : Aditya
@param  : none
@desc   : It's used to register the user via ewallet user registration method
@return none
*/
if(!function_exists('ewalletUserRegistration'))
{
   function ewalletUserRegistration($registration_info=null)
   {
    $obj=& get_instance();
    validRegistrationMethod();
    if(empty($registration_info))
	{
		$registration_info=$obj->session->userdata('registration_info');
	}
	validRegistrationMethod();
	$sponser_id=(!empty($registration_info['sponsor_and_account_info']['ref_id']))?$registration_info['sponsor_and_account_info']['ref_id']:'123456';
	
	$nom_id=(!empty($registration_info['sponsor_and_account_info']['nom_id']))?$registration_info['sponsor_and_account_info']['nom_id']:null;
	
	$leg_posi=(!empty($registration_info['sponsor_and_account_info']['leg_posi']))?$registration_info['sponsor_and_account_info']['leg_posi']:null;
	if(empty($nom_id) || empty($leg_posi))
	{
			 $sponser_details=get_user_details($sponser_id);
			 $leg_posi=$sponser_details->set_leg_position;
			 if(empty($leg_posi) || $leg_posi==null || $leg_posi=='' || $leg_posi=='auto')
			 {
				$ref_id123[]=$sponser_id;
				$leg_posi=getLegPosition1($ref_id123);
				$nom_id=getMatrixNom($ref_id123);
				if($leg_posi=='right')
				{
					$total=$obj->db->select('id')->from('user_registration')->where(array('binary_pos'=>'left','nom_id'=>$nom_id))->get()->num_rows();
					if($total<=0)
					{
						$leg_posi="left";
					}
				}
				$nom_id1=$nom_id;
				$nom_id2=$nom_id;
			 }
			 else 
			 {
				 $nom_id=getNom($sponser_id,$leg_posi);
				 $nom_id1=$nom_id;
				 $nom_id2=$nom_id;
			 }
		
	}//end if
	else 
	{
		$nom_id1=$nom_id;
		$nom_id2=$nom_id;	
	}
	/*********************/
	$pkg_id=(!empty($registration_info['sponsor_and_account_info']['pkg_id']))?$registration_info['sponsor_and_account_info']['pkg_id']:22;
	$pkg_amount=(!empty($registration_info['sponsor_and_account_info']['pkg_amount']))?$registration_info['sponsor_and_account_info']['pkg_amount']:10000;
	$package_fee=$pkg_amount;
	$username=(!empty($registration_info['sponsor_and_account_info']['username']))?$registration_info['sponsor_and_account_info']['username']:'A1';
	$user_password=(!empty($registration_info['sponsor_and_account_info']['password']))?$registration_info['sponsor_and_account_info']['password']:'123';
	$transaction_pwd=(!empty($registration_info['sponsor_and_account_info']['t_code']))?$registration_info['sponsor_and_account_info']['t_code']:'123';
    $user_id=generateUserId();
	//personal informtaion
	$first_name=(!empty($registration_info['personal_info']['first_name']))?$registration_info['personal_info']['first_name']:null;
	$last_name=(!empty($registration_info['personal_info']['last_name']))?$registration_info['personal_info']['last_name']:null;
	$email=(!empty($registration_info['sponsor_and_account_info']['email']))?$registration_info['sponsor_and_account_info']['email']:null;
	$contact_no=(!empty($registration_info['personal_info']['contact_no']))?$registration_info['personal_info']['contact_no']:null;
	$country=(!empty($registration_info['personal_info']['country']))?$registration_info['personal_info']['country']:null;
	$state=(!empty($registration_info['personal_info']['state']))?$registration_info['personal_info']['state']:null;
	$city=(!empty($registration_info['personal_info']['city']))?$registration_info['personal_info']['city']:null;
	$zip_code=(!empty($registration_info['personal_info']['zip_code']))?$registration_info['personal_info']['zip_code']:null;
	$address_line1=(!empty($registration_info['personal_info']['address_line1']))?$registration_info['personal_info']['address_line1']:null;
	$date_of_birth=(!empty($registration_info['personal_info']['date_of_birth']))?$registration_info['personal_info']['date_of_birth']:null;
	//bank account info
	$account_no=(!empty($registration_info['bank_account_info']['account_no']))?$registration_info['bank_account_info']['account_no']:null;
	$branch_name=(!empty($registration_info['bank_account_info']['branch_name']))?$registration_info['bank_account_info']['branch_name']:null;
	$bank_name=(!empty($registration_info['bank_account_info']['bank_name']))?$registration_info['bank_account_info']['bank_name']:null;
	$ifsc_code=(!empty($registration_info['bank_account_info']['ifsc_code']))?$registration_info['bank_account_info']['ifsc_code']:null;
	$account_holder_name=(!empty($registration_info['bank_account_info']['account_holder_name']))?$registration_info['bank_account_info']['account_holder_name']:null;
	/////

    $user_registration_data=array(
    		/*Sponsor and account informtaion*/
    		'user_id'=>$user_id,
    		'ref_id'=>$sponser_id,
    		'nom_id'=>$nom_id,
    		'username'=>$username,
    		'password'=>$user_password,
    		't_code'=>$transaction_pwd,
    		'pkg_id'=>$pkg_id,
    		'pkg_amount'=>$pkg_amount,
			'binary_pos'=>$leg_posi,
    		 /*Personal informtaion*/
    		 'first_name'=>$first_name,
    		 'last_name'=>$last_name,
    		 'email'=>$email,
    		 'contact_no'=>$contact_no,
    		 'country'=>$country,
    		 'state'=>$state,
    		 'city'=>$city,
    		 'zip_code'=>$zip_code,
    		 'address_line1'=>$address_line1,
    		 'address_line1'=>$date_of_birth,
    		 /*Bank Account information*/
    		 'account_no'=>$account_no,
    		 'branch_name'=>$branch_name,
    		 'bank_name'=>$bank_name,
    		 'ifsc_code'=>$ifsc_code,
    		 'account_holder_name'=>$account_holder_name,
    		 ////////
    		 'registration_date'=>date('d-m-Y'),
    		 'current_login_status'=>'0', 
    		 'active_status'=>'1',
			 'registration_method'=>'1',
			 'registration_method_name'=>'E-Wallet'
    		);
    $obj->db->insert('user_registration',$user_registration_data);
    $obj->db->insert('final_e_wallet',array('user_id'=>$user_id,'amount'=>0)); 
	$obj->db->insert('secondry_e_wallet',array('user_id'=>$user_id,'amount'=>0));
	
	//////////////////////////////////////////////////////
	$obj->db->insert('package_sold_amount',array(
	'user_id'=>$user_id,
	'pkg_id'=>$pkg_id,
	'pkg_amount'=>$pkg_amount
	));
	$query_obj=$obj->db->select('amount')->from('final_e_wallet')->where('user_id',$sponser_id)->get()->row();
	$vat=($pkg_amount*5)/100;
	
	
	$balance=$query_obj->amount-($pkg_amount+$vat);
	$obj->db->update('final_e_wallet',array('amount'=>$balance),array('user_id'=>$sponser_id));
	//'1'=>debit for pkg purchased, '2'=> debit for ewallet withdrawl, '3'=>debit for balance transfer, '4'=>'credit for balance transfer received', '5'=>credit for direct commission, '6'=>credit for binary commission, '7'=>credit for matching commission, '9'=>credit for unilevel commission, '10'=>credit for rank bonus update
	/*
	Note:status field '0'=>debit,'1'=>credit
	*/
	$debit_amt=$pkg_amount+$vat;
	$obj->db->insert('credit_debit',array(
	    'transaction_no'=>generateUniqueTranNo(),
	    'user_id'=>$sponser_id,
	    'credit_amt'=>'0',
	    'debit_amt'=>$debit_amt,
	    'balance'=>$balance,
	    'admin_charge'=>'0',
	    'receiver_id'=>$user_id,
	    'sender_id'=>$sponser_id,
	    'receive_date'=>date('d-m-Y'),
	    'ttype'=>'Package Purchased',
	    'TranDescription'=>'Package Purchase by '.$user_id,
	    'Cause'=>'Package Purchase by '.$user_id,
	    'Remark'=>'Package Purchase by '.$user_id,
	    'invoice_no'=>'',
	    'product_name'=>'',
	    'status'=>'0',
	    'ewallet_used_by'=>'Withdrawal Wallet',
	    'current_url'=>ci_site_url(),
	    'reason'=>'1'
        ));
	/////Inserting Data into user_package_log table///////////
	$obj->db->insert('user_package_log',array(
    	'user_id'=>$user_id,
    	'new_package_id'=>$pkg_id,
    	'active_status'=>'1',
    	'purchased_date'=>date('Y-m-d H:i:s')
		));
	/***********Mandatory filed for user registartion in binary plan end over here******************/
	$level=1;
	 ///inserting data into level income binary with status zero from here
	$level_income_binary_data=array();
	$nom_leg_position=$leg_posi;
	while($nom_id!='cmp')
	{
				if($nom_id!='cmp')
				{
					if($nom_id==$sponser_id)
					{
					  $direct_member_leg_position=$leg_posi;
					}
				$level_income_binary_data[]=array('down_id'=>$user_id,'income_id'=>$nom_id,'leg'=>$leg_posi,'status'=>'0','level'=>$level);
				//$obj->db->insert('level_income_binary',array('down_id'=>$user_id,'income_id'=>$nom_id,'leg'=>$leg_posi,'status'=>'0','level'=>$level));
				$level++;
				$query_obj=$obj->db->select('*')->from('user_registration')->where('user_id',$nom_id)->get()->row();
				$leg_posi=$query_obj->binary_pos;
				$nom_id=$query_obj->nom_id;
				}
	}//end while $nom!=cmp
	$obj->db->insert_batch('level_income_binary',$level_income_binary_data);
	$obj->db->update('user_registration',array('direct_member_leg_position'=>$direct_member_leg_position,'nom_leg_position'=>$nom_leg_position),array('user_id'=>$user_id));
	
     /***********Mandatory filed for user registartion in matrix plan end over here******************/
	$l=1;
	$nom_id=$nom_id1;
	while($nom_id!='cmp')
	{
        if($nom_id!='cmp')
        {
			$matrix_downline_data[]=array(
        		'down_id'=>$user_id,
        		'income_id'=>$nom_id,
        		'l_date'=>date('Y-m-d H:i:s'),
        		'status'=>'0',
        		'level'=>$l,
        		'pay_status'=>'Unpaid',
        		'plan_type'=>$pkg_id,
				'binary_pos'=>$nom_leg_position
        		);
			$l++;
             $nom_info=$obj->db->select('nom_id')->from('user_registration')->where('user_id',$nom_id)->get()->row();
             $nom_id=$nom_info->nom_id;
			}
	}	
	$obj->db->insert_batch('matrix_downline',$matrix_downline_data);
	
	$l=1;
	$ref_id=$sponser_id;
	$ref_leg_position=$direct_member_leg_position;
	while($ref_id!='cmp')
	{
        if($ref_id!='cmp')
        {
			$direct_matrix_downline_data[]=array(
        		'down_id'=>$user_id,
        		'income_id'=>$ref_id,
        		'l_date'=>date('Y-m-d H:i:s'),
        		'status'=>'0',
        		//'level'=>level_countdd($user_id,$ref_id),
				'level'=>$l,
        		'pay_status'=>'Unpaid',
        		'plan_type'=>$pkg_id,
				'nom_leg_position'=>$nom_leg_position,
				'ref_leg_position'=>$ref_leg_position
        		);
			$l++;
             $ref_info=$obj->db->select('ref_id,direct_member_leg_position')->from('user_registration')->where('user_id',$ref_id)->get()->row();
             $ref_id=$ref_info->ref_id;
			 $ref_leg_position=$ref_info->direct_member_leg_position;
			}
	}	
	
	$obj->db->insert_batch('direct_matrix_downline',$direct_matrix_downline_data);
	/*Inserting Record of BV in manage bv table for all upliners code starts here*/
	//$upliners=mysql_query("select * from level_income_binary where down_id='$user_id'");
	$upliners_query=$obj->db->select('*')->from('level_income_binary')->where('down_id',$user_id)->get();
	//while($upline=mysql_fetch_array($upliners))
	$bvdata=array();
	foreach($upliners_query->result_array() as $upline)
	{
		$income_id=$upline['income_id'];
		$position=$upline['leg'];
		//$user_level=level_countdd($user_id,$income_id); 
		$user_level=$upline['level']; 
		$bvdata[]=array(
			'income_id'=>$income_id,
			'downline_id'=>$user_id,
			'level'=>$user_level,
			'bv'=>$package_fee,
			'knowledge_points'=>get_knowledege_points($pkg_id),
			'position'=>$position,
			'description'=>'package purchase amount',
			'date'=>date('Y-m-d'),
			'status'=>0,
			);
	}
	if(count($bvdata)>0)
	{
	$obj->db->insert_batch('manage_bv_history',$bvdata);
    }
	/*Inserting Record of BV in manage bv table for all upliners code ends here*/
	//////function call for update the rank of all the upliners as well as provide updated rank bonus
	////function call for credit commission using their sponser_id,pkg id and rank
	$commission_permission=$obj->db->select('status')->from('commission_permission')->where(array('comm_type_id'=>'1', 'pkg_id'=>$pkg_id))->get()->row();//'comm_type_id'=>'1' is used for direct commission type
	
	$package_status=$obj->db->select('status')->from('package')->where('id', $pkg_id)->get()->row();

	if($commission_permission->status=='1' && !empty($package_status->status) && $package_status->status=='1')
	{
	$pkg_name=get_package_name($pkg_id);
	creditDirectCommission($sponser_id,$pkg_id,$user_id,$pkg_name);
    }

	$commission_permission=$obj->db->select('status')->from('commission_permission')->where(array('comm_type_id'=>'4', 'pkg_id'=>$pkg_id))->get()->row();//'comm_type_id'=>'1' is used for unilevel commission type
	if($commission_permission->status=='1' && !empty($package_status->status) && $package_status->status=='1')
	{
	creditUnilevelCommission($pkg_id,$user_id,$package_fee);
    }

	$sponsor_user_name=get_user_name($sponser_id);
	sendWelcomeEmailToUser($user_id,$username,$user_password,$transaction_pwd,$email,$sponsor_user_name,$pkg_amount);
	$upliner_name=get_user_name($nom_id2);
	sendNewRegistrationEmailToAdmin($user_id,$username,$user_password,$sponsor_user_name,$upliner_name,$email);

	return true;
   }//end function
}//end function exists0
/*
@author : Aditya
@param  : none
@desc   : It's used to register the user via ewallet user registration method
@return none
*/
if(!function_exists('epinUserRegistration'))
{
   function epinUserRegistration($registration_info=null)
   {
    $obj=& get_instance();
	validRegistrationMethod();
   
    if(empty($registration_info))
	{
		$registration_info=$obj->session->userdata('registration_info');
	}
	validRegistrationMethod();
	$sponser_id=(!empty($registration_info['sponsor_and_account_info']['ref_id']))?$registration_info['sponsor_and_account_info']['ref_id']:'123456';
	
	$nom_id=(!empty($registration_info['sponsor_and_account_info']['nom_id']))?$registration_info['sponsor_and_account_info']['nom_id']:null;
	
	$leg_posi=(!empty($registration_info['sponsor_and_account_info']['leg_posi']))?$registration_info['sponsor_and_account_info']['leg_posi']:null;
	if(empty($nom_id) || empty($leg_posi))
	{
			// $leg_posi=(!empty($registration_info['sponsor_and_account_info']['ref_leg_position']))?$registration_info['sponsor_and_account_info']['ref_leg_position']:null;
			 $sponser_details=get_user_details($sponser_id);
			 $leg_posi=$sponser_details->set_leg_position;
			// $leg_posi='right';
			 if(empty($leg_posi) || $leg_posi==null || $leg_posi=='' || $leg_posi=='auto')
			 {
				$ref_id123[]=$sponser_id;
				$leg_posi=getLegPosition1($ref_id123);
				$nom_id=getMatrixNom($ref_id123);
				if($leg_posi=='right')
				{
					$total=$obj->db->select('id')->from('user_registration')->where(array('binary_pos'=>'left','nom_id'=>$nom_id))->get()->num_rows();
					if($total<=0)
					{
						$leg_posi="left";
					}
				}
				$nom_id1=$nom_id;
				$nom_id2=$nom_id;
			 }
			 else 
			 {
				 $nom_id=getNom($sponser_id,$leg_posi);
				 $nom_id1=$nom_id;
				 $nom_id2=$nom_id;
			 }
		
	}//end if
	else 
	{
		$nom_id1=$nom_id;
		$nom_id2=$nom_id;	
	}
	 ////////////////////////////////////
	$pkg_id=(!empty($registration_info['sponsor_and_account_info']['pkg_id']))?$registration_info['sponsor_and_account_info']['pkg_id']:22;
	$pkg_amount=(!empty($registration_info['sponsor_and_account_info']['pkg_amount']))?$registration_info['sponsor_and_account_info']['pkg_amount']:10000;
	$package_fee=$pkg_amount;
	$username=(!empty($registration_info['sponsor_and_account_info']['username']))?$registration_info['sponsor_and_account_info']['username']:'A1';
	$user_password=(!empty($registration_info['sponsor_and_account_info']['password']))?$registration_info['sponsor_and_account_info']['password']:'123';
	$transaction_pwd=(!empty($registration_info['sponsor_and_account_info']['t_code']))?$registration_info['sponsor_and_account_info']['t_code']:'123';
    $user_id=generateUserId();
	
	
	//personal informtaion
	$first_name=(!empty($registration_info['personal_info']['first_name']))?$registration_info['personal_info']['first_name']:null;
	$last_name=(!empty($registration_info['personal_info']['last_name']))?$registration_info['personal_info']['last_name']:null;
	$email=(!empty($registration_info['sponsor_and_account_info']['email']))?$registration_info['sponsor_and_account_info']['email']:null;
	$contact_no=(!empty($registration_info['personal_info']['contact_no']))?$registration_info['personal_info']['contact_no']:null;
	$country=(!empty($registration_info['personal_info']['country']))?$registration_info['personal_info']['country']:null;
	$state=(!empty($registration_info['personal_info']['state']))?$registration_info['personal_info']['state']:null;
	$city=(!empty($registration_info['personal_info']['city']))?$registration_info['personal_info']['city']:null;
	$zip_code=(!empty($registration_info['personal_info']['zip_code']))?$registration_info['personal_info']['zip_code']:null;
	$address_line1=(!empty($registration_info['personal_info']['address_line1']))?$registration_info['personal_info']['address_line1']:null;
	$date_of_birth=(!empty($registration_info['personal_info']['date_of_birth']))?$registration_info['personal_info']['date_of_birth']:null;
	//bank account info
	$account_no=(!empty($registration_info['bank_account_info']['account_no']))?$registration_info['bank_account_info']['account_no']:null;
	$branch_name=(!empty($registration_info['bank_account_info']['branch_name']))?$registration_info['bank_account_info']['branch_name']:null;
	$bank_name=(!empty($registration_info['bank_account_info']['bank_name']))?$registration_info['bank_account_info']['bank_name']:null;
	$ifsc_code=(!empty($registration_info['bank_account_info']['ifsc_code']))?$registration_info['bank_account_info']['ifsc_code']:null;
	$account_holder_name=(!empty($registration_info['bank_account_info']['account_holder_name']))?$registration_info['bank_account_info']['account_holder_name']:null;
	/////
	$registration_info['sponsor_and_account_info']['account_type'];
	
    $user_registration_data=array(
    		/*Sponsor and account informtaion*/
    		'user_id'=>$user_id,
    		'ref_id'=>$sponser_id,
    		'nom_id'=>$nom_id,
    		'username'=>$username,
    		'password'=>$user_password,
    		't_code'=>$transaction_pwd,
    		'pkg_id'=>$pkg_id,
    		'pkg_amount'=>$pkg_amount,
			'binary_pos'=>$leg_posi,
    		 /*Personal informtaion*/
    		 'first_name'=>$first_name,
    		 'last_name'=>$last_name,
    		 'email'=>$email,
    		 'contact_no'=>$contact_no,
    		 'country'=>$country,
    		 'state'=>$state,
    		 'city'=>$city,
    		 'zip_code'=>$zip_code,
    		 'address_line1'=>$address_line1,
    		 'address_line1'=>$date_of_birth,
    		 /*Bank Account information*/
    		 'account_no'=>$account_no,
    		 'branch_name'=>$branch_name,
    		 'bank_name'=>$bank_name,
    		 'ifsc_code'=>$ifsc_code,
    		 'account_holder_name'=>$account_holder_name,
    		 ////////
    		 'registration_date'=>date('d-m-Y'),
    		 'current_login_status'=>'0', 
    		 'active_status'=>'1',
			 'registration_method'=>'2',
			 'registration_method_name'=>'E-Pin'
    		);
    $obj->db->insert('user_registration',$user_registration_data);
    $obj->db->insert('final_e_wallet',array('user_id'=>$user_id,'amount'=>0)); 
	$obj->db->insert('secondry_e_wallet',array('user_id'=>$user_id,'amount'=>0));
	
	//////////////////////////////////////////////////////
	$obj->db->insert('package_sold_amount',array(
	'user_id'=>$user_id,
	'pkg_id'=>$pkg_id,
	'pkg_amount'=>$pkg_amount
	));
	
	/////Inserting Data into user_package_log table///////////
	$obj->db->insert('user_package_log',array(
    	'user_id'=>$user_id,
    	'new_package_id'=>$pkg_id,
    	'active_status'=>'1',
    	'purchased_date'=>date('Y-m-d H:i:s')
		));
	/***********Mandatory filed for user registartion in binary plan end over here******************/
	$level=1;
	 ///inserting data into level income binary with status zero from here
	$level_income_binary_data=array();
	$nom_leg_position=$leg_posi;
	while($nom_id!='cmp')
	{
				if($nom_id!='cmp')
				{
				    if($nom_id==$sponser_id)
					{
						$direct_member_leg_position=$leg_posi;
					}	
				$level_income_binary_data[]=array('down_id'=>$user_id,'income_id'=>$nom_id,'leg'=>$leg_posi,'status'=>'0','level'=>$level);
				//$obj->db->insert('level_income_binary',array('down_id'=>$user_id,'income_id'=>$nom_id,'leg'=>$leg_posi,'status'=>'0','level'=>$level));
				$level++;
				$query_obj=$obj->db->select('*')->from('user_registration')->where('user_id',$nom_id)->get()->row();
				$leg_posi=$query_obj->binary_pos;
				$nom_id=$query_obj->nom_id;
				}
	}//end while $nom!=cmp
	$obj->db->insert_batch('level_income_binary',$level_income_binary_data);
    $obj->db->update('user_registration',array('direct_member_leg_position'=>$direct_member_leg_position,'nom_leg_position'=>$nom_leg_position),array('user_id'=>$user_id));
	/***********Mandatory filed for user registartion in matrix plan end over here******************/
    $l=1;
	$nom_id=$nom_id1;
	while($nom_id!='cmp')
	{
        if($nom_id!='cmp')
        {
        	$matrix_downline_data[]=array(
        		'down_id'=>$user_id,
        		'income_id'=>$nom_id,
        		'l_date'=>date('Y-m-d H:i:s'),
        		'status'=>'0',
        		'level'=>$l,
        		'pay_status'=>'Unpaid',
        		'plan_type'=>$pkg_id,
				'binary_pos'=>$nom_leg_position
        		);
			$l++;
             $nom_info=$obj->db->select('nom_id')->from('user_registration')->where('user_id',$nom_id)->get()->row();
             $nom_id=$nom_info->nom_id;
			}
	}	
	$obj->db->insert_batch('matrix_downline',$matrix_downline_data);
	$l=1;
	$ref_id=$sponser_id;
	$ref_leg_position=$direct_member_leg_position;
	while($ref_id!='cmp')
	{
        if($ref_id!='cmp')
        {
			$direct_matrix_downline_data[]=array(
        		'down_id'=>$user_id,
        		'income_id'=>$ref_id,
        		'l_date'=>date('Y-m-d H:i:s'),
        		'status'=>'0',
        		//'level'=>level_countdd($user_id,$ref_id),
				'level'=>$l,
        		'pay_status'=>'Unpaid',
        		'plan_type'=>$pkg_id,
				'nom_leg_position'=>$nom_leg_position,
				'ref_leg_position'=>$ref_leg_position
        		);
			$l++;
             $ref_info=$obj->db->select('ref_id,direct_member_leg_position')->from('user_registration')->where('user_id',$ref_id)->get()->row();
             $ref_id=$ref_info->ref_id;
			 $ref_leg_position=$ref_info->direct_member_leg_position;
			}
	}	
	$obj->db->insert_batch('direct_matrix_downline',$direct_matrix_downline_data);
	
	
	/*Inserting Record of BV in manage bv table for all upliners code starts here*/
	//$upliners=mysql_query("select * from level_income_binary where down_id='$user_id'");
	$upliners_query=$obj->db->select('*')->from('level_income_binary')->where('down_id',$user_id)->get();
	//while($upline=mysql_fetch_array($upliners))
	$bvdata=array();
	foreach($upliners_query->result_array() as $upline)
	{
		$income_id=$upline['income_id'];
		$position=$upline['leg'];
		//$user_level=level_countdd($user_id,$income_id); 
		$user_level=$upline['level']; 
		
		$bvdata[]=array(
			'income_id'=>$income_id,
			'downline_id'=>$user_id,
			'level'=>$user_level,
			'bv'=>$package_fee,
			'knowledge_points'=>get_knowledege_points($pkg_id),
			'position'=>$position,
			'description'=>'package purchase amount',
			'date'=>date('Y-m-d'),
			'status'=>0,
			);
	}
	if(count($bvdata)>0)
	{
	$obj->db->insert_batch('manage_bv_history',$bvdata);
    }
	/*Inserting Record of BV in manage bv table for all upliners code ends here*/
	//////function call for update the rank of all the upliners as well as provide 
	////function call for credit commission using their sponser_id,pkg id and rank
	$commission_permission=$obj->db->select('status')->from('commission_permission')->where(array('comm_type_id'=>'1', 'pkg_id'=>$pkg_id))->get()->row();//'comm_type_id'=>'1' is used for direct commission type
	$package_status=$obj->db->select('status')->from('package')->where('id', $pkg_id)->get()->row();

	if($commission_permission->status=='1' && !empty($package_status->status) && $package_status->status=='1')
	{
	$pkg_name=get_package_name($pkg_id);
	creditDirectCommission($sponser_id,$pkg_id,$user_id,$pkg_name);
    }

	$commission_permission=$obj->db->select('status')->from('commission_permission')->where(array('comm_type_id'=>'4', 'pkg_id'=>$pkg_id))->get()->row();//'comm_type_id'=>'1' is used for unilevel commission type
	if($commission_permission->status=='1' && !empty($package_status->status) && $package_status->status=='1')
	{
	creditUnilevelCommission($pkg_id,$user_id,$package_fee);
    }

	$sponsor_user_name=get_user_name($sponser_id);
	sendWelcomeEmailToUser($user_id,$username,$user_password,$transaction_pwd,$email,$sponsor_user_name,$pkg_amount);
	$upliner_name=get_user_name($nom_id2);
	sendNewRegistrationEmailToAdmin($user_id,$username,$user_password,$sponsor_user_name,$upliner_name,$email);

	return true;
   }//end function
}//end function exists0
/*
@author : Aditya
@param  : none
@desc   : It's used to register the user via ewallet user registration method
@return none
*/
if(!function_exists('bankWireUserRegistration'))
{
   function bankWireUserRegistration($id)
   {
	$obj= get_instance();
    $obj->db->update('bank_wired_user_registration',array('status'=>'1','action_date'=>date('Y-m-d H:i:s')),array('id'=>$id));	
    $register_user_details=$obj->db->select('*')->from('bank_wired_user_registration')->where('id',$id)->get()->row();
    $sponser_id=$register_user_details->ref_id;
	///////////////////////////////////////////////////////
	$nom_id=(!empty($register_user_details->nom_id))?$register_user_details->nom_id:null;
	$leg_posi=(!empty($register_user_details->leg_posi))?$register_user_details->leg_posi:null;
	if(empty($nom_id) || empty($leg_posi))
	{
			$sponser_details=get_user_details($sponser_id);
			$leg_posi=$sponser_details->set_leg_position;
			if(empty($leg_posi) || $leg_posi==null || $leg_posi=='' || $leg_posi=='auto')
			 {
				$ref_id123[]=$sponser_id;
				$leg_posi=getLegPosition1($ref_id123);
				$nom_id=getMatrixNom($ref_id123);
				if($leg_posi=='right')
				{
					$total=$obj->db->select('id')->from('user_registration')->where(array('binary_pos'=>'left','nom_id'=>$nom_id))->get()->num_rows();
					if($total<=0)
					{
						$leg_posi="left";
					}
				}
				$nom_id1=$nom_id;
				$nom_id2=$nom_id;
			 }
			 else 
			 {
				 $nom_id=getNom($sponser_id,$leg_posi);
				 $nom_id1=$nom_id;
				 $nom_id2=$nom_id;
			 }
		
	}//end if
	else 
	{
		$nom_id1=$nom_id;
		$nom_id2=$nom_id;	
	}
	 //////////////////////////////
    $pkg_id=$register_user_details->platform;
    $pkg_amount=$register_user_details->package_fee;
    $package_fee=$pkg_amount;
    $username=$register_user_details->username;
    $user_password=$register_user_details->password;
    $transaction_pwd=$register_user_details->t_code;
    $user_id=generateUserId();
    /*Personal informtaion*/
    $first_name=(!empty($register_user_details->first_name))?$register_user_details->first_name:null;
    $last_name=(!empty($register_user_details->last_name))?$register_user_details->last_name:null;
    $email=(!empty($register_user_details->email))?$register_user_details->email:null;
    $contact_no=(!empty($register_user_details->contact_no))?$register_user_details->contact_no:null;
    $country=(!empty($register_user_details->country))?$register_user_details->country:null;
    $state=(!empty($register_user_details->state))?$register_user_details->state:null;
    $city=(!empty($register_user_details->city))?$register_user_details->city:null;
    $zip_code=(!empty($register_user_details->zip_code))?$register_user_details->zip_code:null;
    $address_line1=(!empty($register_user_details->address_line1))?$register_user_details->address_line1:null;
    $date_of_birth=(!empty($register_user_details->date_of_birth))?$register_user_details->date_of_birth:null;

    /*Bank Account information*/
    $account_no=(!empty($register_user_details->account_no))?$register_user_details->account_no:null;
    $branch_name=(!empty($register_user_details->branch_name))?$register_user_details->branch_name:null;
    $bank_name=(!empty($register_user_details->bank_name))?$register_user_details->bank_name:null;
    $ifsc_code=(!empty($register_user_details->ifsc_code))?$register_user_details->ifsc_code:null;
    $account_holder_name=(!empty($register_user_details->account_holder_name))?$register_user_details->account_holder_name:null;
	///////
    $user_registration_data=array(
    		/*Sponsor and account informtaion*/
    		'user_id'=>$user_id,
    		'ref_id'=>$sponser_id,
    		'nom_id'=>$nom_id,
    		'username'=>$username,
    		'password'=>$user_password,
    		't_code'=>$transaction_pwd,
    		'pkg_id'=>$pkg_id,
    		'pkg_amount'=>$pkg_amount,
			'binary_pos'=>$leg_posi,
    		 /*Personal informtaion*/
    		 'first_name'=>$first_name,
    		 'last_name'=>$last_name,
    		 'email'=>$email,
    		 'contact_no'=>$contact_no,
    		 'country'=>$country,
    		 'state'=>$state,
    		 'city'=>$city,
    		 'zip_code'=>$zip_code,
    		 'address_line1'=>$address_line1,
    		 'date_of_birth'=>$date_of_birth,
    		 /*Bank Account information*/
    		 'account_no'=>$account_no,
    		 'branch_name'=>$branch_name,
    		 'bank_name'=>$bank_name,
    		 'ifsc_code'=>$ifsc_code,
    		 'account_holder_name'=>$account_holder_name,
    		 ////////
    		 'registration_date'=>date('d-m-Y'),
    		 'current_login_status'=>'0', 
    		 'active_status'=>'1',
			 'registration_method'=>'3',
			 'registration_method_name'=>'Bank-Wire'
    		);
    $obj->db->insert('user_registration',$user_registration_data);
    $obj->db->insert('final_e_wallet',array('user_id'=>$user_id,'amount'=>0));
	$obj->db->insert('secondry_e_wallet',array('user_id'=>$user_id,'amount'=>0));
		
	//////////////////////////////////////////////////////
	$obj->db->insert('package_sold_amount',array(
	'user_id'=>$user_id,
	'pkg_id'=>$pkg_id,
	'pkg_amount'=>$pkg_amount
	));
	
	$obj->db->insert('user_package_log',array(
    	'user_id'=>$user_id,
    	'new_package_id'=>$pkg_id,
    	'active_status'=>'1',
    	'purchased_date'=>date('Y-m-d H:i:s')
		));
	/***********Mandatory filed for user registartion in binary plan end over here******************/
	$level=1;
	 ///inserting data into level income binary with status zero from here
	$level_income_binary_data=array();
	$nom_leg_position=$leg_posi;
	while($nom_id!='cmp')
	{
				if($nom_id!='cmp')
				{
					if($nom_id==$sponser_id)
					{
						$direct_member_leg_position=$leg_posi;
					}	
				$level_income_binary_data[]=array('down_id'=>$user_id,'income_id'=>$nom_id,'leg'=>$leg_posi,'status'=>'0','level'=>$level);
				//$obj->db->insert('level_income_binary',array('down_id'=>$user_id,'income_id'=>$nom_id,'leg'=>$leg_posi,'status'=>'0','level'=>$level));
				$level++;
				$query_obj=$obj->db->select('*')->from('user_registration')->where('user_id',$nom_id)->get()->row();
				$leg_posi=$query_obj->binary_pos;
				$nom_id=$query_obj->nom_id;
				}
	}//end while $nom!=cmp
	$obj->db->insert_batch('level_income_binary',$level_income_binary_data);
	$obj->db->update('user_registration',array('direct_member_leg_position'=>$direct_member_leg_position,'nom_leg_position'=>$nom_leg_position),array('user_id'=>$user_id));
	

    
     /***********Mandatory filed for user registartion in matrix plan end over here******************/
    $l=1;
	$nom_id=$nom_id1;
	while($nom_id!='cmp')
	{
        if($nom_id!='cmp')
        {
        	$matrix_downline_data[]=array(
        		'down_id'=>$user_id,
        		'income_id'=>$nom_id,
        		'l_date'=>date('Y-m-d H:i:s'),
        		'status'=>'0',
        		'level'=>$l,
        		'pay_status'=>'Unpaid',
        		'plan_type'=>$pkg_id,
				'binary_pos'=>$nom_leg_position
        		);
			$l++;
             $nom_info=$obj->db->select('nom_id')->from('user_registration')->where('user_id',$nom_id)->get()->row();
             $nom_id=$nom_info->nom_id;
			}
	}	
	$obj->db->insert_batch('matrix_downline',$matrix_downline_data);
	$l=1;
	$ref_id=$sponser_id;
	$ref_leg_position=$direct_member_leg_position;
	while($ref_id!='cmp')
	{
        if($ref_id!='cmp')
        {
			$direct_matrix_downline_data[]=array(
        		'down_id'=>$user_id,
        		'income_id'=>$ref_id,
        		'l_date'=>date('Y-m-d H:i:s'),
        		'status'=>'0',
        		//'level'=>level_countdd($user_id,$ref_id),
				'level'=>$l,
        		'pay_status'=>'Unpaid',
        		'plan_type'=>$pkg_id,
				'nom_leg_position'=>$nom_leg_position,
				'ref_leg_position'=>$ref_leg_position
        		);
			$l++;
             $ref_info=$obj->db->select('ref_id,direct_member_leg_position')->from('user_registration')->where('user_id',$ref_id)->get()->row();
             $ref_id=$ref_info->ref_id;
			 $ref_leg_position=$ref_info->direct_member_leg_position;
			}
	}	
	$obj->db->insert_batch('direct_matrix_downline',$direct_matrix_downline_data);
	
	
	/*Inserting Record of BV in manage bv table for all upliners code starts here*/
	//$upliners=mysql_query("select * from level_income_binary where down_id='$user_id'");
	$upliners_query=$obj->db->select('*')->from('level_income_binary')->where('down_id',$user_id)->get();
	//while($upline=mysql_fetch_array($upliners))
	$bvdata=array();
	foreach($upliners_query->result_array() as $upline)
	{
		$income_id=$upline['income_id'];
		$position=$upline['leg'];
		//$user_level=level_countdd($user_id,$income_id); 
		$user_level=$upline['level']; 
		$bvdata[]=array(
			'income_id'=>$income_id,
			'downline_id'=>$user_id,
			'level'=>$user_level,
			'bv'=>$package_fee,
			'knowledge_points'=>get_knowledege_points($pkg_id),
			'position'=>$position,
			'description'=>'package purchase amount',
			'date'=>date('Y-m-d'),
			'status'=>0,
			);
	}
	if(count($bvdata)>0)
	{
	$obj->db->insert_batch('manage_bv_history',$bvdata);
    }
	/*Inserting Record of BV in manage bv table for all upliners code ends here*/
	//////function call for update the rank of all the upliners as well as provide 
	////function call for credit commission using their sponser_id,pkg id and rank
	$commission_permission=$obj->db->select('status')->from('commission_permission')->where(array('comm_type_id'=>'1', 'pkg_id'=>$pkg_id))->get()->row();//'comm_type_id'=>'1' is used for direct commission type
	$package_status=$obj->db->select('status')->from('package')->where('id', $pkg_id)->get()->row();

	if($commission_permission->status=='1' && !empty($package_status->status) && $package_status->status=='1')
	{
	$pkg_name=get_package_name($pkg_id);	
	creditDirectCommission($sponser_id,$pkg_id,$user_id,$pkg_name);
    }

	$commission_permission=$obj->db->select('status')->from('commission_permission')->where(array('comm_type_id'=>'4', 'pkg_id'=>$pkg_id))->get()->row();//'comm_type_id'=>'1' is used for unilevel commission type
	if($commission_permission->status=='1' && !empty($package_status->status) && $package_status->status=='1')
	{
	creditUnilevelCommission($pkg_id,$user_id,$package_fee);
    }
	$sponsor_user_name=get_user_name($sponser_id);
	sendWelcomeEmailToUser($user_id,$username,$user_password,$transaction_pwd,$email,$sponsor_user_name,$pkg_amount);
	$upliner_name=get_user_name($nom_id2);
	sendNewRegistrationEmailToAdmin($user_id,$username,$user_password,$sponsor_user_name,$upliner_name,$email);
	return true;
   }//end function
}//end function exists0

function payStackUserRegistration()
{
	$obj=& get_instance();
    validRegistrationMethod();
    if(empty($registration_info))
	{
		$registration_info=$obj->session->userdata('registration_info');
	}
	$sponser_id=(!empty($registration_info['sponsor_and_account_info']['ref_id']))?$registration_info['sponsor_and_account_info']['ref_id']:'9848665';
	 
	$nom_id=(!empty($registration_info['sponsor_and_account_info']['nom_id']))?$registration_info['sponsor_and_account_info']['nom_id']:null;
	
	$leg_posi=(!empty($registration_info['sponsor_and_account_info']['leg_posi']))?$registration_info['sponsor_and_account_info']['leg_posi']:null;
	if(empty($nom_id) || empty($leg_posi))
	{
			// $leg_posi=(!empty($registration_info['sponsor_and_account_info']['ref_leg_position']))?$registration_info['sponsor_and_account_info']['ref_leg_position']:null;
			 $sponser_details=get_user_details($sponser_id);
			 $leg_posi=$sponser_details->set_leg_position;
			// $leg_posi='right';
			 if(empty($leg_posi) || $leg_posi==null || $leg_posi=='' || $leg_posi=='auto')
			 {
				$ref_id123[]=$sponser_id;
				$leg_posi=getLegPosition1($ref_id123);
				$nom_id=getMatrixNom($ref_id123);
				if($leg_posi=='right')
				{
					$total=$obj->db->select('id')->from('user_registration')->where(array('binary_pos'=>'left','nom_id'=>$nom_id))->get()->num_rows();
					if($total<=0)
					{
						$leg_posi="left";
					}
				}
				$nom_id1=$nom_id;
				$nom_id2=$nom_id;
			 }
			 else 
			 {
				 $nom_id=getNom($sponser_id,$leg_posi);
				 $nom_id1=$nom_id;
				 $nom_id2=$nom_id;
			 }
		
	}//end if
	else 
	{
		$nom_id1=$nom_id;
		$nom_id2=$nom_id;	
	}
	/*********************/
	$pkg_id=(!empty($registration_info['sponsor_and_account_info']['pkg_id']))?$registration_info['sponsor_and_account_info']['pkg_id']:22;
	$pkg_amount=(!empty($registration_info['sponsor_and_account_info']['pkg_amount']))?$registration_info['sponsor_and_account_info']['pkg_amount']:100;
	$package_fee=$pkg_amount;
	$username=(!empty($registration_info['sponsor_and_account_info']['username']))?$registration_info['sponsor_and_account_info']['username']:'A1';
	$user_password=(!empty($registration_info['sponsor_and_account_info']['password']))?$registration_info['sponsor_and_account_info']['password']:'123';
	$transaction_pwd=(!empty($registration_info['sponsor_and_account_info']['t_code']))?$registration_info['sponsor_and_account_info']['t_code']:'123';
    $user_id=generateUserId();
	//personal informtaion
	$first_name=(!empty($registration_info['personal_info']['first_name']))?$registration_info['personal_info']['first_name']:null;
	$last_name=(!empty($registration_info['personal_info']['last_name']))?$registration_info['personal_info']['last_name']:null;
	$email=(!empty($registration_info['sponsor_and_account_info']['email']))?$registration_info['sponsor_and_account_info']['email']:null;
	$contact_no=(!empty($registration_info['personal_info']['contact_no']))?$registration_info['personal_info']['contact_no']:null;
	$country=(!empty($registration_info['personal_info']['country']))?$registration_info['personal_info']['country']:null;
	$state=(!empty($registration_info['personal_info']['state']))?$registration_info['personal_info']['state']:null;
	$city=(!empty($registration_info['personal_info']['city']))?$registration_info['personal_info']['city']:null;
	$zip_code=(!empty($registration_info['personal_info']['zip_code']))?$registration_info['personal_info']['zip_code']:null;
	$address_line1=(!empty($registration_info['personal_info']['address_line1']))?$registration_info['personal_info']['address_line1']:null;
	$date_of_birth=(!empty($registration_info['personal_info']['date_of_birth']))?$registration_info['personal_info']['date_of_birth']:null;
	//bank account info
	$account_no=(!empty($registration_info['bank_account_info']['account_no']))?$registration_info['bank_account_info']['account_no']:null;
	$branch_name=(!empty($registration_info['bank_account_info']['branch_name']))?$registration_info['bank_account_info']['branch_name']:null;
	$bank_name=(!empty($registration_info['bank_account_info']['bank_name']))?$registration_info['bank_account_info']['bank_name']:null;
	$ifsc_code=(!empty($registration_info['bank_account_info']['ifsc_code']))?$registration_info['bank_account_info']['ifsc_code']:null;
	$account_holder_name=(!empty($registration_info['bank_account_info']['account_holder_name']))?$registration_info['bank_account_info']['account_holder_name']:null;
	/////

    $user_registration_data=array(
    		/*Sponsor and account informtaion*/
    		'user_id'=>$user_id,
    		'ref_id'=>$sponser_id,
    		'nom_id'=>$nom_id,
    		'username'=>$username,
    		'password'=>$user_password,
    		't_code'=>$transaction_pwd,
    		'pkg_id'=>$pkg_id,
    		'pkg_amount'=>$pkg_amount,
			'binary_pos'=>$leg_posi,
    		 /*Personal informtaion*/
    		 'first_name'=>$first_name,
    		 'last_name'=>$last_name,
    		 'email'=>$email,
    		 'contact_no'=>$contact_no,
    		 'country'=>$country,
    		 'state'=>$state,
    		 'city'=>$city,
    		 'zip_code'=>$zip_code,
    		 'address_line1'=>$address_line1,
    		 'address_line1'=>$date_of_birth,
    		 /*Bank Account information*/
    		 'account_no'=>$account_no,
    		 'branch_name'=>$branch_name,
    		 'bank_name'=>$bank_name,
    		 'ifsc_code'=>$ifsc_code,
    		 'account_holder_name'=>$account_holder_name,
    		 ////////
    		 'registration_date'=>date('d-m-Y'),
    		 'current_login_status'=>'0', 
    		 'active_status'=>'1',
			 'registration_method'=>'1',
			 'registration_method_name'=>'pay-stack'
    		);
    $obj->db->insert('user_registration',$user_registration_data);
    $obj->db->insert('final_e_wallet',array('user_id'=>$user_id,'amount'=>0)); 
	$obj->db->insert('secondry_e_wallet',array('user_id'=>$user_id,'amount'=>0));
	
	//////////////////////////////////////////////////////
	$obj->db->insert('package_sold_amount',array(
	'user_id'=>$user_id,
	'pkg_id'=>$pkg_id,
	'pkg_amount'=>$pkg_amount
	));
	
	/////Inserting Data into user_package_log table///////////
	$obj->db->insert('user_package_log',array(
    	'user_id'=>$user_id,
    	'new_package_id'=>$pkg_id,
    	'active_status'=>'1',
    	'purchased_date'=>date('Y-m-d H:i:s')
		));
	/***********Mandatory filed for user registartion in binary plan end over here******************/
	$level=1;
	 ///inserting data into level income binary with status zero from here
	$level_income_binary_data=array();
	$nom_leg_position=$leg_posi;
	while($nom_id!='cmp')
	{
				if($nom_id!='cmp')
				{
					if($nom_id==$sponser_id)
					{
						$direct_member_leg_position=$leg_posi;
					}	
				$level_income_binary_data[]=array('down_id'=>$user_id,'income_id'=>$nom_id,'leg'=>$leg_posi,'status'=>'0','level'=>$level);
				//$obj->db->insert('level_income_binary',array('down_id'=>$user_id,'income_id'=>$nom_id,'leg'=>$leg_posi,'status'=>'0','level'=>$level));
				$level++;
				$query_obj=$obj->db->select('*')->from('user_registration')->where('user_id',$nom_id)->get()->row();
				$leg_posi=$query_obj->binary_pos;
				$nom_id=$query_obj->nom_id;
				}
	}//end while $nom!=cmp
	$obj->db->insert_batch('level_income_binary',$level_income_binary_data);
	
	$obj->db->update('user_registration',array('direct_member_leg_position'=>$direct_member_leg_position,'nom_leg_position'=>$nom_leg_position),array('user_id'=>$user_id));
	

    
     /***********Mandatory filed for user registartion in matrix plan end over here******************/
    $l=1;
	$nom_id=$nom_id1;
	while($nom_id!='cmp')
	{
        if($nom_id!='cmp')
        {
        	$matrix_downline_data[]=array(
        		'down_id'=>$user_id,
        		'income_id'=>$nom_id,
        		'l_date'=>date('Y-m-d H:i:s'),
        		'status'=>'0',
        		'level'=>$l,
        		'pay_status'=>'Unpaid',
        		'plan_type'=>$pkg_id,
				'binary_pos'=>$nom_leg_position
        		);
			$l++;
             $nom_info=$obj->db->select('nom_id')->from('user_registration')->where('user_id',$nom_id)->get()->row();
             $nom_id=$nom_info->nom_id;
			}
	}	
	$obj->db->insert_batch('matrix_downline',$matrix_downline_data);
	$l=1;
	$ref_id=$sponser_id;
	$ref_leg_position=$direct_member_leg_position;
	while($ref_id!='cmp')
	{
        if($ref_id!='cmp')
        {
			$direct_matrix_downline_data[]=array(
        		'down_id'=>$user_id,
        		'income_id'=>$ref_id,
        		'l_date'=>date('Y-m-d H:i:s'),
        		'status'=>'0',
        		//'level'=>level_countdd($user_id,$ref_id),
				'level'=>$l,
        		'pay_status'=>'Unpaid',
        		'plan_type'=>$pkg_id,
				'nom_leg_position'=>$nom_leg_position,
				'ref_leg_position'=>$ref_leg_position
        		);
			$l++;
             $ref_info=$obj->db->select('ref_id,direct_member_leg_position')->from('user_registration')->where('user_id',$ref_id)->get()->row();
             $ref_id=$ref_info->ref_id;
			 $ref_leg_position=$ref_info->direct_member_leg_position;
			}
	}	
	$obj->db->insert_batch('direct_matrix_downline',$direct_matrix_downline_data);
	
	
	/*Inserting Record of BV in manage bv table for all upliners code starts here*/
	//$upliners=mysql_query("select * from level_income_binary where down_id='$user_id'");
	$upliners_query=$obj->db->select('*')->from('level_income_binary')->where('down_id',$user_id)->get();
	//while($upline=mysql_fetch_array($upliners))
	$bvdata=array();
	foreach($upliners_query->result_array() as $upline)
	{
		$income_id=$upline['income_id'];
		$position=$upline['leg'];
		//$user_level=level_countdd($user_id,$income_id); 
		$user_level=$upline['level']; 
		$bvdata[]=array(
			'income_id'=>$income_id,
			'downline_id'=>$user_id,
			'level'=>$user_level,
			'bv'=>$package_fee,
			'knowledge_points'=>get_knowledege_points($pkg_id),
			'position'=>$position,
			'description'=>'package purchase amount',
			'date'=>date('Y-m-d'),
			'status'=>0,
			);
	}
	if(count($bvdata)>0)
	{
	$obj->db->insert_batch('manage_bv_history',$bvdata);
    }
	////function call for credit commission using their sponser_id,pkg id and rank
	$commission_permission=$obj->db->select('status')->from('commission_permission')->where(array('comm_type_id'=>'1', 'pkg_id'=>$pkg_id))->get()->row();//'comm_type_id'=>'1' is used for direct commission type
	$package_status=$obj->db->select('status')->from('package')->where('id', $pkg_id)->get()->row();

	if($commission_permission->status=='1' && !empty($package_status->status) && $package_status->status=='1')
	{
	$pkg_name=get_package_name($pkg_id);	
	creditDirectCommission($sponser_id,$pkg_id,$user_id,$pkg_name);
    }

	$commission_permission=$obj->db->select('status')->from('commission_permission')->where(array('comm_type_id'=>'4', 'pkg_id'=>$pkg_id))->get()->row();//'comm_type_id'=>'1' is used for unilevel commission type
	if($commission_permission->status=='1' && !empty($package_status->status) && $package_status->status=='1')
	{
	creditUnilevelCommission($pkg_id,$user_id,$package_fee);
    }

	$sponsor_user_name=get_user_name($sponser_id);
	sendWelcomeEmailToUser($user_id,$username,$user_password,$transaction_pwd,$email,$sponsor_user_name,$pkg_amount);
	$upliner_name=get_user_name($nom_id2);
	sendNewRegistrationEmailToAdmin($user_id,$username,$user_password,$sponsor_user_name,$upliner_name,$email);
	return true;
}
function sendWelcomeEmailToUser($user_id,$username,$password,$transaction_pwd,$email,$sponsor_user_name,$pkg_amount)
{

	$email_data=array();
	$email_data['from']='info@merignos.com';
	$email_data['to']=$email;
	$email_data['subject']='Registration Successful on Merigoans';
	$email_data['user_id']=$user_id;
	$email_data['username']=$username;
	$email_data['password']=$password;
	$email_data['transaction_pwd']=$transaction_pwd;
	$email_data['email']=$email;
	$email_data['sponsor_user_name']=$sponsor_user_name;
	$email_data['pkg_amount']=$pkg_amount;
	$email_data['vat']=($pkg_amount*5)/100;
	$email_data['email-template']='welcome-email';
	_sendEmail($email_data);
}//end function 
function sendNewRegistrationEmailToAdmin($user_id,$username,$password,$sponsor_username,$upliner,$email)
{

    $email_data['from']='info@globalsoftwebtechnologies.com';
    $email_data['to']='victorglobalmlm@gmail.com';
    $email_data['subject']='New member registration on Global MLM';
    
    $email_data['template_header_msg']='New Member is Registered on your site <a target="_blank" href="'.ci_site_url().'">'.ci_site_url().'</a>';
    $email_data['user_id']=$user_id;
    $email_data['username']=$username;
    $email_data['password']=$password;
    $email_data['sponsor_username']=$sponsor_username;
    $email_data['upliner']=$upliner;
    $email_data['email']=$email;
    $email_data['email-template']='email-to-admin';
    _sendEmail($email_data);
}//end function
function update_rank($user_id)
{
	$obj=& get_instance();
	/*@package purchased freedom*/
	$left_diginity=$obj->db->select('id')->from('user_registration as u')
	->where(array('u.ref_id'=>$user_id,'u.pkg_id >='=>'23','u.active_status'=>'1','direct_member_leg_position'=>'left','rank_id'=>'0'))->get()->num_rows();
	/*@package purchased freedom*/
	$right_diginity=$obj->db->select('id')->from('user_registration as u')
	->where(array('u.ref_id'=>$user_id,'u.pkg_id >='=>'23','u.active_status'=>'1','direct_member_leg_position'=>'right','rank_id'=>'0'))->get()->num_rows();
	
	$total_dignity=0;
	if($left_diginity>0 && $right_diginity>0)
	{
	$total_dignity=$left_diginity+$right_diginity;
	}
	////////////////////////////////////////////////////////
	
	$left_prestige=$obj->db->select('u.id')->from('user_registration as u')->where(array('u.ref_id'=>$user_id,'u.rank_id >='=>'1','u.active_status'=>'1','direct_member_leg_position'=>'left'))->get()->num_rows();
	
	$right_prestige=$obj->db->select('u.id')->from('user_registration as u')->where(array('u.ref_id'=>$user_id,'u.rank_id >='=>'1','u.active_status'=>'1','direct_member_leg_position'=>'right'))->get()->num_rows();
	
	$total_prestige=0;
	if($left_prestige>0 && $right_prestige>0)
	{
	$total_prestige=$left_prestige+$right_prestige;
	}
	////////////////////////////////////
	
	$left_luxury=$obj->db->select('u.id')->from('user_registration as u')->where(array('u.ref_id'=>$user_id,'u.rank_id >='=>'2','u.active_status'=>'1','direct_member_leg_position'=>'left'))->get()->num_rows();
	
	$right_luxury=$obj->db->select('u.id')->from('user_registration as u')->where(array('u.ref_id'=>$user_id,'u.rank_id >='=>'2','u.active_status'=>'1','direct_member_leg_position'=>'right'))->get()->num_rows();
	
	$total_luxury=0;
	if($left_luxury>0 && $right_luxury>0)
	{
	$total_luxury=$left_luxury+$right_luxury;
	}
	///////////////////////////////////
	
	$left_influencer=$obj->db->select('u.id')->from('user_registration as u')->where(array('u.ref_id'=>$user_id,'u.rank_id >='=>'3','u.active_status'=>'1','direct_member_leg_position'=>'left'))->get()->num_rows();
	
	$right_influencer=$obj->db->select('u.id')->from('user_registration as u')->where(array('u.ref_id'=>$user_id,'u.rank_id >='=>'3','u.active_status'=>'1','direct_member_leg_position'=>'right'))->get()->num_rows();
	
	$total_influencer=0;
	if($left_influencer>0 && $right_influencer>0)
	{
	  $total_influencer=$left_influencer+$right_influencer;	
	}
	/////////////////////////	
	$left_royalty=$obj->db->select('u.id')->from('user_registration as u')->where(array('u.ref_id'=>$user_id,'u.rank_id >='=>'4','u.active_status'=>'1','direct_member_leg_position'=>'left'))->get()->num_rows();
	
	$right_royalty=$obj->db->select('u.id')->from('user_registration as u')->where(array('u.ref_id'=>$user_id,'u.rank_id >='=>'4','u.active_status'=>'1','direct_member_leg_position'=>'right'))->get()->num_rows();
	
	$total_royalty=0;
	
	if($left_royalty>0 && $right_royalty>0)
	{
		$total_royalty=$left_royalty+$right_royalty;
	}
	/////////////////////////////////
	$user_details=get_user_details($user_id);
	//////////////////
	if($total_royalty>=2)//2 influencer
	{
	   if($user_details->rank_id!='5')
	   {
		//////////////
		$rank_update=false;
		$required_knowledge_points=650000;
		$total_knowledge_points=get_total_kw($user_id,$required_knowledge_points);
		if($total_knowledge_points>=650000)
		{
			$rank_update=true;
		}
		if($rank_update)
		  {
			$obj->db->update('user_registration',array('rank_id'=>'5','rank_name'=>'Royalty'),array('user_id'=>$user_id));
			
			$obj->db->insert('rank_award',array(
				'user_id'=>$user_id,
				'rank_name'=>'Royalty',
				'rank_award'=>'House (N10M)'
				));
				
			$obj->db->update('rank_knowledge_points',array('status'=>'paid'),array('income_id'=>$user_id,'rank_knowledge_points'=>'Unpaid'));
		  }
		  
	   }
	}
	else if($total_influencer>=2)///2 luxury
	{
	   if($user_details->rank_id!='4')
	   {
			$rank_update=false;
			//////////////
			$required_knowledge_points=250000;
			$total_knowledge_points=get_total_kw($user_id,$required_knowledge_points);
			if($total_knowledge_points>=250000)
			{
				$rank_update=true;
			}
		   if($rank_update)
		   {
			   $obj->db->update('user_registration',array('rank_id'=>'4','rank_name'=>'Influencer'),array('user_id'=>$user_id));
			   
			   $obj->db->insert('rank_award',array(
				'user_id'=>$user_id,
				'rank_name'=>'Influencer',
				'rank_award'=>'Car (N4M)'
				));
				
				$obj->db->update('rank_knowledge_points',array('status'=>'paid'),array('income_id'=>$user_id,'rank_knowledge_points'=>'Unpaid'));
		   }
	   }
	}
	else if($total_luxury>=2)//2 prestige
	{
	   if($user_details->rank_id!='3')
	   {
		   $rank_update=false;
		   //////////////
		   $required_knowledge_points=100000;
		   $total_knowledge_points=get_total_kw($user_id,$required_knowledge_points);
			if($total_knowledge_points>=100000)
			{
				$rank_update=true;
			}
		   if($rank_update)
		   {
			   $obj->db->update('user_registration',array('rank_id'=>'3','rank_name'=>'Luxury'),array('user_id'=>$user_id));
			   
			   $obj->db->insert('rank_award',array(
				'user_id'=>$user_id,
				'rank_name'=>'Luxury',
				'rank_award'=>'Vacation (N1M)'
				));
				
				$obj->db->update('rank_knowledge_points',array('status'=>'paid'),array('income_id'=>$user_id,'rank_knowledge_points'=>'Unpaid'));
			   
		   }
	   }
		
	}
	else if($total_prestige>=2)//2 diginity
	{
		
	   if($user_details->rank_id!='2')
	   {
		   $rank_update=false;
		   $required_knowledge_points=25000;
		   $total_knowledge_points=get_total_kw($user_id,$required_knowledge_points);
		   if($total_knowledge_points>=25000)
			{
				$rank_update=true;
			}
		   if($rank_update)
		   {
			  $obj->db->update('user_registration',array('rank_id'=>'2','rank_name'=>'Prestige'),array('user_id'=>$user_id));
			  
			   $obj->db->insert('rank_award',array(
				'user_id'=>$user_id,
				'rank_name'=>'Prestige',
				'rank_award'=>'2000K'
				));
				
				$obj->db->update('rank_knowledge_points',array('status'=>'paid'),array('income_id'=>$user_id,'rank_knowledge_points'=>'Unpaid'));
		   }
	   }
	}
	else if($total_dignity>=2)
	{
	  
	   if($user_details->rank_id!='1')
	   {
		   
			$rank_update=false;
			//////////////
			$required_knowledge_points=10000;
			/////////////////////////////
			$total_knowledge_points=get_total_kw($user_id,$required_knowledge_points);
			if($total_knowledge_points>=10000)
			{
				$rank_update=true;
			}
			///////////////////////////////////////////////
			if($rank_update)
			{
				$obj->db->update('user_registration',array('rank_id'=>'1','rank_name'=>'Dignity'),array('user_id'=>$user_id));
				
				$obj->db->insert('rank_award',array(
				'user_id'=>$user_id,
				'rank_name'=>'Dignity',
				'rank_award'=>'Gift item + 50K'
				));
				$obj->db->update('rank_knowledge_points',array('status'=>'paid'),array('income_id'=>$user_id,'rank_knowledge_points'=>'Unpaid'));
			}
	   }
	}
}//end function
function get_kw($user_id,$required_knowledge_points)
{
    	   $obj=&get_instance();
    	   $all_downliner=$obj->db->select('*')->from('rank_knowledge_points')->where(array('income_id'=>$user_id,'position !'=>'self'))->get()->result();
			$total_kw=0;
			foreach($all_downliner as $row)
			{
			    $total_kw=$total_kw+$row->rank_knowledge_points;
			    
			}
			if($total_kw>=$required_knowledge_points)
			{
			    $total_kw=($total_kw*40)/100;
			}
			return $total_kw;
}
function get_total_kw($user_id,$required_knowledge_points)
{
			$obj=&get_instance();
	/////////////////////////////
			
		   $kw_info=$obj->db->select_sum('rank_knowledge_points')->from('rank_knowledge_points')->where(array('income_id'=>$user_id,'position !'=>'self'))->get()->row();
		   
		   $total_kw_point=$kw_info->rank_knowledge_points;
		   if($total_kw_point>=$required_knowledge_points)
			{
			    $total_kw_point=($total_kw_point*40)/100;
			}
			
			
			$all_downliner=$obj->db->select('downline_id')->from('rank_knowledge_points')->where(array('income_id'=>$user_id,'position !'=>'self'))->get()->result();
			
			foreach($all_downliner as $row)
			{
			    $kw_point=get_kw($row->downline_id,$required_knowledge_points);
			    $total_kw_point=$total_kw_point+$kw_point;
			}
			
			
			$self_kw_info=$obj->db->select_sum('rank_knowledge_points')->from('rank_knowledge_points')->where(array('income_id'=>$user_id,'position'=>'self'))->get()->row();
			
			$self_kw=$self_kw_info->rank_knowledge_points;
			
			$total_knowledge_points=$total_kw_point+$self_kw;
			$total_knowledge_points=(!empty($total_knowledge_points))?$total_knowledge_points:0;
			return $total_knowledge_points;
	
}
if(!function_exists('testRegister'))
{
   function testRegister($registration_info=null)
   {
    $obj=& get_instance();
    //$registerData=$obj->session->all_userdata();//open  and close comment
     /***********Mandatory filed for user registartion in binary plan start from here******************/
    ////user_registration query
    /*Sponsor and account informtaion*/
    if(empty($registration_info))
	{
		$registration_info=$obj->session->userdata('registration_info');
	}
	$sponser_id=(!empty($registration_info['sponsor_and_account_info']['ref_id']))?$registration_info['sponsor_and_account_info']['ref_id']:'123456';
	 
	// $leg_posi=(!empty($registration_info['sponsor_and_account_info']['ref_leg_position']))?$registration_info['sponsor_and_account_info']['ref_leg_position']:null;
	 
	 $sponser_details=get_user_details($sponser_id);
	 $leg_posi=$sponser_details->set_leg_position;
	 $leg_posi='left';
	 if(empty($leg_posi) || $leg_posi==null || $leg_posi=='' || $leg_posi=='auto')
     {
     	$ref_id123[]=$sponser_id;
		$leg_posi=getLegPosition1($ref_id123);
		$nom_id=getMatrixNom($ref_id123);
		if($leg_posi=='right')
		{
			$total=$obj->db->select('id')->from('user_registration')->where(array('binary_pos'=>'left','nom_id'=>$nom_id))->get()->num_rows();
			if($total<=0)
			{
				$leg_posi="left";
			}
		}
		$nom_id1=$nom_id;
		$nom_id2=$nom_id;
	 }
	 else 
	 {
		 $nom_id=getNom($sponser_id,$leg_posi);
		 $nom_id1=$nom_id;
		 $nom_id2=$nom_id;
	 }
	/*********************/
	$pkg_id=(!empty($registration_info['sponsor_and_account_info']['pkg_id']))?$registration_info['sponsor_and_account_info']['pkg_id']:22;
	$pkg_amount=(!empty($registration_info['sponsor_and_account_info']['pkg_amount']))?$registration_info['sponsor_and_account_info']['pkg_amount']:10000;
	$package_fee=$pkg_amount;
	$username=(!empty($registration_info['sponsor_and_account_info']['username']))?$registration_info['sponsor_and_account_info']['username']:'A1';
	$user_password=(!empty($registration_info['sponsor_and_account_info']['password']))?$registration_info['sponsor_and_account_info']['password']:'123';
	$transaction_pwd=(!empty($registration_info['sponsor_and_account_info']['t_code']))?$registration_info['sponsor_and_account_info']['t_code']:'123';
    $user_id=generateUserId();
	//personal informtaion
	$first_name=(!empty($registration_info['personal_info']['first_name']))?$registration_info['personal_info']['first_name']:null;
	$last_name=(!empty($registration_info['personal_info']['last_name']))?$registration_info['personal_info']['last_name']:null;
	$email=(!empty($registration_info['sponsor_and_account_info']['email']))?$registration_info['sponsor_and_account_info']['email']:null;
	$contact_no=(!empty($registration_info['personal_info']['contact_no']))?$registration_info['personal_info']['contact_no']:null;
	$country=(!empty($registration_info['personal_info']['country']))?$registration_info['personal_info']['country']:null;
	$state=(!empty($registration_info['personal_info']['state']))?$registration_info['personal_info']['state']:null;
	$city=(!empty($registration_info['personal_info']['city']))?$registration_info['personal_info']['city']:null;
	$zip_code=(!empty($registration_info['personal_info']['zip_code']))?$registration_info['personal_info']['zip_code']:null;
	$address_line1=(!empty($registration_info['personal_info']['address_line1']))?$registration_info['personal_info']['address_line1']:null;
	$date_of_birth=(!empty($registration_info['personal_info']['date_of_birth']))?$registration_info['personal_info']['date_of_birth']:null;
	//bank account info
	$account_no=(!empty($registration_info['bank_account_info']['account_no']))?$registration_info['bank_account_info']['account_no']:null;
	$branch_name=(!empty($registration_info['bank_account_info']['branch_name']))?$registration_info['bank_account_info']['branch_name']:null;
	$bank_name=(!empty($registration_info['bank_account_info']['bank_name']))?$registration_info['bank_account_info']['bank_name']:null;
	$ifsc_code=(!empty($registration_info['bank_account_info']['ifsc_code']))?$registration_info['bank_account_info']['ifsc_code']:null;
	$account_holder_name=(!empty($registration_info['bank_account_info']['account_holder_name']))?$registration_info['bank_account_info']['account_holder_name']:null;
	/////

    $user_registration_data=array(
    		/*Sponsor and account informtaion*/
    		'user_id'=>$user_id,
    		'ref_id'=>$sponser_id,
    		'nom_id'=>$nom_id,
    		'username'=>$username,
    		'password'=>$user_password,
    		't_code'=>$transaction_pwd,
    		'pkg_id'=>$pkg_id,
    		'pkg_amount'=>$pkg_amount,
			'binary_pos'=>$leg_posi,
    		 /*Personal informtaion*/
    		 'first_name'=>$first_name,
    		 'last_name'=>$last_name,
    		 'email'=>$email,
    		 'contact_no'=>$contact_no,
    		 'country'=>$country,
    		 'state'=>$state,
    		 'city'=>$city,
    		 'zip_code'=>$zip_code,
    		 'address_line1'=>$address_line1,
    		 'address_line1'=>$date_of_birth,
    		 /*Bank Account information*/
    		 'account_no'=>$account_no,
    		 'branch_name'=>$branch_name,
    		 'bank_name'=>$bank_name,
    		 'ifsc_code'=>$ifsc_code,
    		 'account_holder_name'=>$account_holder_name,
    		 ////////
    		 'registration_date'=>date('d-m-Y'),
    		 'current_login_status'=>'0', 
    		 'active_status'=>'1',
			 'registration_method'=>'1',
			 'registration_method_name'=>'E-Wallet'
    		);
    $obj->db->insert('user_registration',$user_registration_data);
    $obj->db->insert('final_e_wallet',array('user_id'=>$user_id,'amount'=>0)); 
	$obj->db->insert('secondry_e_wallet',array('user_id'=>$user_id,'amount'=>0));
	
	//////////////////////////////////////////////////////
	
	$obj->db->insert('package_sold_amount',array(
	'user_id'=>$user_id,
	'pkg_id'=>$pkg_id,
	'pkg_amount'=>$pkg_amount
	));
	/////Inserting Data into user_package_log table///////////
	$obj->db->insert('user_package_log',array(
    	'user_id'=>$user_id,
    	'new_package_id'=>$pkg_id,
    	'active_status'=>'1',
    	'purchased_date'=>date('Y-m-d H:i:s')
		));
	/***********Mandatory filed for user registartion in binary plan end over here******************/
	$level=1;
	 ///inserting data into level income binary with status zero from here
	$level_income_binary_data=array();
	$nom_leg_position=$leg_posi;
	while($nom_id!='cmp')
	{
				if($nom_id!='cmp')
				{
					if($nom_id==$sponser_id)
					{
					  $direct_member_leg_position=$leg_posi;
					}
				$level_income_binary_data[]=array('down_id'=>$user_id,'income_id'=>$nom_id,'leg'=>$leg_posi,'status'=>'0','level'=>$level);
				//$obj->db->insert('level_income_binary',array('down_id'=>$user_id,'income_id'=>$nom_id,'leg'=>$leg_posi,'status'=>'0','level'=>$level));
				$level++;
				$query_obj=$obj->db->select('*')->from('user_registration')->where('user_id',$nom_id)->get()->row();
				$leg_posi=$query_obj->binary_pos;
				$nom_id=$query_obj->nom_id;
				}
	}//end while $nom!=cmp
	$obj->db->insert_batch('level_income_binary',$level_income_binary_data);
	$obj->db->update('user_registration',array('direct_member_leg_position'=>$direct_member_leg_position,'nom_leg_position'=>$nom_leg_position),array('user_id'=>$user_id));
	
    
     /***********Mandatory filed for user registartion in matrix plan end over here******************/
    $l=1;
	$nom_id=$nom_id1;
	while($nom_id!='cmp')
	{
        if($nom_id!='cmp')
        {
			$matrix_downline_data[]=array(
        		'down_id'=>$user_id,
        		'income_id'=>$nom_id,
        		'l_date'=>date('Y-m-d H:i:s'),
        		'status'=>'0',
        		'level'=>$l,
        		'pay_status'=>'Unpaid',
        		'plan_type'=>$pkg_id,
				'binary_pos'=>$nom_leg_position
        		);
			$l++;
             $nom_info=$obj->db->select('nom_id')->from('user_registration')->where('user_id',$nom_id)->get()->row();
             $nom_id=$nom_info->nom_id;
			}
	}	
	$obj->db->insert_batch('matrix_downline',$matrix_downline_data);
	$l=1;
	$ref_id=$sponser_id;
	while($ref_id!='cmp')
	{
        if($ref_id!='cmp')
        {
			$direct_matrix_downline_data[]=array(
        		'down_id'=>$user_id,
        		'income_id'=>$ref_id,
        		'l_date'=>date('Y-m-d H:i:s'),
        		'status'=>'0',
        		//'level'=>level_countdd($user_id,$ref_id),
				'level'=>$l,
        		'pay_status'=>'Unpaid',
        		'plan_type'=>$pkg_id,
				'binary_pos'=>$nom_leg_position
        		);
			$l++;
             $ref_info=$obj->db->select('ref_id')->from('user_registration')->where('user_id',$ref_id)->get()->row();
             $ref_id=$ref_info->ref_id;
			}
	}	
	$obj->db->insert_batch('direct_matrix_downline',$direct_matrix_downline_data);
	
	
	/*Inserting Record of BV in manage bv table for all upliners code starts here*/
	//$upliners=mysql_query("select * from level_income_binary where down_id='$user_id'");
	$upliners_query=$obj->db->select('*')->from('level_income_binary')->where('down_id',$user_id)->get();
	//while($upline=mysql_fetch_array($upliners))
	$bvdata=array();
	foreach($upliners_query->result_array() as $upline)
	{
		$income_id=$upline['income_id'];
		$position=$upline['leg'];
		//$user_level=level_countdd($user_id,$income_id); 
		$user_level=$upline['level']; 
		$bvdata[]=array(
			'income_id'=>$income_id,
			'downline_id'=>$user_id,
			'level'=>$user_level,
			'bv'=>$package_fee,
			'knowledge_points'=>get_knowledege_points($pkg_id),
			'position'=>$position,
			'description'=>'package purchase amount',
			'date'=>date('Y-m-d'),
			'status'=>0,
			);
	}
	if(count($bvdata)>0)
	{
	$obj->db->insert_batch('manage_bv_history',$bvdata);
    }
	
	/*Inserting Record of BV in manage bv table for all upliners code ends here*/
	//////function call for update the rank of all the upliners as well as provide updated rank bonus
	////function call for credit commission using their sponser_id,pkg id and rank
	
	$commission_permission=$obj->db->select('status')->from('commission_permission')->where(array('comm_type_id'=>'1', 'pkg_id'=>$pkg_id))->get()->row();//'comm_type_id'=>'1' is used for direct commission type
	
	$package_status=$obj->db->select('status')->from('package')->where('id', $pkg_id)->get()->row();

	if($commission_permission->status=='1' && !empty($package_status->status) && $package_status->status=='1')
	{
	$pkg_name=get_package_name($pkg_id);
	creditDirectCommission($sponser_id,$pkg_id,$user_id,$pkg_name);
    }

	$commission_permission=$obj->db->select('status')->from('commission_permission')->where(array('comm_type_id'=>'4', 'pkg_id'=>$pkg_id))->get()->row();//'comm_type_id'=>'1' is used for unilevel commission type
	if($commission_permission->status=='1' && !empty($package_status->status) && $package_status->status=='1')
	{
	creditUnilevelCommission($pkg_id,$user_id,$package_fee);
    }

	$sponsor_user_name=get_user_name($sponser_id);
	sendWelcomeEmailToUser($user_id,$username,$user_password,$transaction_pwd,$email,$sponsor_user_name);
	$upliner_name=get_user_name($nom_id2);
	sendNewRegistrationEmailToAdmin($user_id,$username,$user_password,$sponsor_user_name,$upliner_name,$email);
	
	return true;
   }//end function
}//end function exists0
?>
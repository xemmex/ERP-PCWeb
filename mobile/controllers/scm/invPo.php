<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
//采购订单
//150501
class InvPo extends CI_Controller {
    public function __construct(){
        parent::__construct();
        $this->common_model->checkpurview();
        $this->jxcsys  = $this->session->userdata('jxcsys');
    }
    public function index() {
        $action = $this->input->get('action',TRUE);
        switch ($action) {
            case 'initPo':
                $this->common_model->checkpurview(2);
                $this->load->view('scm/invPo/initPo');
                break;
            case 'editPo':
                $this->common_model->checkpurview(1);
                $this->load->view('scm/invPo/initPo');
                break;
            case 'initPoList':
                $this->common_model->checkpurview(1);
                $this->load->view('scm/invPo/initPoList');
                break;
            default:
                $this->common_model->checkpurview(1);
                $this->poList();
        }
    }

    public function poList() {
        $v = array();
        $data['status'] = 200;
        $data['msg']    = 'success';
        $page = max(intval($this->input->get_post('page',TRUE)),1);
        $rows = max(intval($this->input->get_post('rows',TRUE)),100);
        $sidx = str_enhtml($this->input->get_post('sidx',TRUE));
        $sord = str_enhtml($this->input->get_post('sord',TRUE));
        $transType = intval($this->input->get_post('transType',TRUE));
        if(empty($transType)){$transType='150501';}
        $matchCon  = str_enhtml($this->input->get_post('matchCon',TRUE));
        $beginDate = str_enhtml($this->input->get_post('beginDate',TRUE));
        $endDate   = str_enhtml($this->input->get_post('endDate',TRUE));
        $order = $sidx ? $sidx.' '.$sord :' a.id desc';
        $where = ' and a.billType="PUR"';
        $where .= $transType > 0  ? ' and a.transType='.$transType : '';
        $where .= $matchCon  ? ' and (b.name like "%'.$matchCon.'%" or description like "%'.$matchCon.'%" or billNo like "%'.$matchCon.'%")' : '';
        $where .= $beginDate ? ' and a.billDate>="'.$beginDate.'"' : '';
        $where .= $endDate ? ' and a.billDate<="'.$endDate.'"' : '';
        $offset = $rows * ($page-1);
        $data['data']['page']      = $page;
        $data['data']['records']   = $this->data_model->get_order($where,3);                             //总条数
        $data['data']['total']     = ceil($data['data']['records']/$rows);                                 //总分页数

        //exit($where.' order by '.$order.' limit '.$offset.','.$rows.'');

        $list = $this->data_model->get_order($where.' order by '.$order.' limit '.$offset.','.$rows.'');
        //exit(var_dump($list));
        foreach ($list as $arr=>$row) {
            $v[$arr]['id']           = intval($row['id']);
            $v[$arr]['checkName']    = $row['checkName'];
            $v[$arr]['checked']      = intval($row['checked']);
            $v[$arr]['billDate']     = $row['billDate'];
            $v[$arr]['hxStateCode']  = intval($row['hxStateCode']);
            $v[$arr]['amount']       = (float)abs($row['amount']);
            $v[$arr]['transType']    = intval($row['transType']);
            $v[$arr]['rpAmount']     = (float)abs($row['rpAmount']);
            $v[$arr]['currency']     = $row['currency'];
            $v[$arr]['contactName']  = $row['contactName'];//$row['contactNo'].' '.$row['contactName'];
            $v[$arr]['description']  = $row['description'];
            $v[$arr]['billNo']       = $row['billNo'];
            $v[$arr]['totalAmount']  = (float)abs($row['totalAmount']);
            $v[$arr]['userName']     = $row['userName'];
            $v[$arr]['transTypeName']= $row['transTypeName'];
            $v[$arr]['disEditable']  = 0;
            $v[$arr]['totalQty']       = $row['totalQty'];
            $v[$arr]['deliveryDate']       = $row['deliveryDate'];
            $v[$arr]['checkName']       = $row['checkName'];
            if($row['billStatus']==2)
            {
                $v[$arr]['billStatusName'] = "全部入库";
            }
            elseif ($row['billStatus']==1)
            {
                $v[$arr]['billStatusName'] = "部分入库";
            }
            else
            {
                $v[$arr]['billStatusName'] = "未入库";
            }
        }
        $data['data']['rows']        = $v;
        die(json_encode($data));
    }

    //导出
    public function exportInvPo(){
        $this->common_model->checkpurview(5);
        $name = 'po_record_'.date('YmdHis').'.xls';
        sys_csv($name);
        $this->common_model->logs('导出采购单据:'.$name);
        $sidx = str_enhtml($this->input->get_post('sidx',TRUE));
        $sord = str_enhtml($this->input->get_post('sord',TRUE));
        $transType = intval($this->input->get_post('transType',TRUE));
        if(empty($transType)){$transType='150501';}
        $matchCon  = str_enhtml($this->input->get_post('matchCon',TRUE));
        $beginDate = str_enhtml($this->input->get_post('beginDate',TRUE));
        $endDate   = str_enhtml($this->input->get_post('endDate',TRUE));
        $order = $sidx ? $sidx.' '.$sord :' a.id desc';
        $where = ' and a.billType="PUR"';
        $where .= $transType>0  ? ' and a.transType='.$transType : '';
        $where .= $matchCon  ? ' and (b.name like "%'.$matchCon.'%" or description like "%'.$matchCon.'%" or billNo like "%'.$matchCon.'%")' : '';
        $where .= $beginDate ? ' and a.billDate>="'.$beginDate.'"' : '';
        $where .= $endDate ? ' and a.billDate<="'.$endDate.'"' : '';
        $where1 = ' and a.billType="PUR"';
        $where1 .= $transType>0  ? ' and a.transType='.$transType : '';
        $where1 .= $beginDate ? ' and a.billDate>="'.$beginDate.'"' : '';
        $where1 .= $endDate ? ' and a.billDate<="'.$endDate.'"' : '';
        $data['list1'] = $this->data_model->get_order($where.' order by '.$order.'');
        $data['list2'] = $this->data_model->get_order_info($where1.' order by a.billDate');
        //exit(print_r($data));
        $this->load->view('scm/invPo/exportInvPo',$data);
    }

    //新增
    public function add(){
        $this->common_model->checkpurview(2);
        $data = $this->input->post('postData',TRUE);
        if (strlen($data)>0) {
            $data = (array)json_decode($data, true);
            $data = $this->validform($data);
            $info = elements(array(
                'billNo',
                'billType',
                'transType',
                'transTypeName',
                'buId',
                'billDate',
                'description',
                'totalQty',
                'amount',
                'arrears',
                'rpAmount',
                //'currency',
                'totalAmount',
                'hxStateCode',
                'totalArrears',
                'disRate',
                'disAmount',
                'uid',
                'userName',
                'accId',
                'modifyTime',
                'locationId',
                'deliveryDate',
                'orderType',
                'paymentMethod',
                'shippingMethod',
                'currency'
            ),$data);
            $this->db->trans_begin();
            //exit(print_r($info));
            $info['salesId']=$data['uid'];
            $iid = $this->mysql_model->insert(ORDER,$info);
            if(empty($iid))
            {
                $sql=$this->db->last_query();
                exit("order更新错误".$sql);
            }
            $this->order_info($iid,$data);
            $this->account_info($iid,$data);
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $sql=$this->db->last_query();
                //exit($sql);
                str_alert(-1,$sql);
                //str_alert(-1,'SQL错误');
            } else {
                $this->db->trans_commit();
                $this->common_model->logs('新增采购 单据编号：'.$info['billNo']);
                str_alert(200,'success',array('id'=>intval($iid)));
            }
        }
        str_alert(-1,'提交的是空数据');
    }

    //新增
    public function addnew(){
        $this->add();
    }

    //修改保存
    public function updateInvPo(){
        $this->common_model->checkpurview(3);
        $data = $this->input->post('postData',TRUE);
        if (strlen($data)>0) {
            $data = $this->validform((array)json_decode($data, true));
            $info = elements(array(
                'billType',
                'transType',
                'transTypeName',
                'buId',
                'billDate',
                'description',
                'totalQty',
                'amount',
                'arrears',
                'rpAmount',
                //'currency',
                'totalAmount',
                'hxStateCode',
                'totalArrears',
                'disRate',
                'disAmount',
                'uid',
                'userName',
                'accId',
                'modifyTime',
                'orderType',
                'paymentMethod',
                'shippingMethod',
                'currency',
                'locationId'
            ),$data);
            $this->db->trans_begin();
            $this->mysql_model->update(ORDER,$info,'(id='.$data['id'].')');
            $this->order_info($data['id'],$data);
            $this->account_info($data['id'],$data);
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                str_alert(-1,'SQL错误');
            } else {
                $this->db->trans_commit();
                $this->common_model->logs('修改采购单 单据编号：'.$data['billNo']);
                str_alert(200,'success',array('id'=>$data['id']));
            }
        }
        str_alert(-1,'提交的数据不能为空');
    }


    //获取修改信息
    public function update() {
        $this->common_model->checkpurview(1);
        //$id   = intval($this->input->get_post('id',TRUE));

        $id   = intval($_REQUEST['id']);
        $condition="";
        if(!empty($id))
        {
            $condition .= " and (a.id=$id.)";
        }
        if(!empty($_REQUEST['billNo']))
        {
            $condition .= " and (a.billNo='".$_REQUEST[billNo]."')";
        }

        //exit($condition);
        //$data =  $this->data_model->get_order('and (a.id='.$id.') and billType="PUR"',1);
        $data =  $this->data_model->get_order($condition.' and billType="PUR"',1);
        //exit(print_r($data));

        if (count($data)>0) {
            $s = $v = array();
            $info['status'] = 200;
            $info['msg']    = 'success';
            $id = $info['data']['id'] = intval($data['id']);
            $info['data']['buId']               = intval($data['buId']);
            //供应商公司名
            $info['data']['contactName']        = $data['contactName'];
            $info['data']['date']               = $data['billDate'];
            $info['data']['billNo']             = $data['billNo'];
            $info['data']['billType']           = $data['billType'];
            $info['data']['modifyTime']         = $data['modifyTime'];
            $info['data']['checkName']          = $data['checkName'];
            $info['data']['transType']          = intval($data['transType']);
            $info['data']['totalQty']           = (float)$data['totalQty'];
            $info['data']['totalTaxAmount']     = (float)$data['totalTaxAmount'];
            $info['data']['billStatus']         = intval($data['billStatus']);
            $info['data']['disRate']            = (float)$data['disRate'];
            $info['data']['disAmount']          = (float)$data['disAmount'];
            $info['data']['amount']             = (float)abs($data['amount']);
            $info['data']['rpAmount']           = (float)abs($data['rpAmount']);
            $info['data']['arrears']            = (float)abs($data['arrears']);
            $info['data']['userName']           = $data['userName'];
            $info['data']['checked']            = intval($data['checked']);
            $info['data']['status']             = intval($data['checked'])==1 ? 'view' : 'edit';    //edit
            $info['data']['totalDiscount']      = (float)$data['totalDiscount'];
            $info['data']['totalTax']           = (float)$data['totalTax'];
            $info['data']['totalAmount']        = (float)abs($data['totalAmount']);
            $info['data']['description']        = $data['description'];
            $info['data']['orderType']        = intval($data['orderType']);
            $info['data']['paymentMethod']        = intval($data['paymentMethod']);
            $info['data']['shippingMethod']        = intval($data['shippingMethod']);
            $info['data']['deliveryDate']        = $data['deliveryDate'];
            $info['data']['currency']        = intval($data['currency']);
            $info['data']['currencyCode']        = $data['currencyCode'];
            $info['data']['currencyText']        = $data['currencyText'];
            $info['data']['accId']        = $data['accId'];
            $info['data']['locationName']        = $data['locationName'];
            $info['data']['locationNo']        = $data['locationNo'];
            $info['data']['locationId']        = intval($data['locationId']);

            $list = $this->data_model->get_order_info('and (iid='.$id.') order by id');
            //exit(print_r($list));
            foreach ($list as $arr=>$row) {
                $v[$arr]['spec']             = $row['invSpec'];
                $v[$arr]['srcEntryId']     = $row['srcEntryId'];
                $v[$arr]['srcBillNo']          = $row['srcBillNo'];
                $v[$arr]['srcId']          = $row['srcId'];
                $v[$arr]['goods']               = $row['invName'];
                $v[$arr]['invName']             = $row['invNumber'];
                $v[$arr]['qty']                 = (float)abs($row['qty']);
                $v[$arr]['stockQty']            = abs($row['stockQty']);
                $v[$arr]['amount']              = (float)abs($row['amount']);
                $v[$arr]['taxAmount']           = (float)abs($row['taxAmount']);
                $v[$arr]['price']               = (float)$row['price'];
                $v[$arr]['tax']                 = (float)$row['tax'];
                $v[$arr]['taxRate']             = (float)$row['taxRate'];
                $v[$arr]['currencyCode']        = $row['currencyCode'];
                $v[$arr]['mainUnit']            = $row['mainUnit'];
                $v[$arr]['deduction']           = (float)$row['deduction'];
                $v[$arr]['invId']               = intval($row['invId']);
                $v[$arr]['invNumber']           = $row['invNumber'];
                $v[$arr]['locationId']          = intval($row['locationId']);
                $v[$arr]['locationName']        = $row['locationName'];
                $v[$arr]['discountRate']        = $row['discountRate'];
                $v[$arr]['unitId']              = intval($row['unitId']);
                $v[$arr]['description']         = $row['description'];
                $v[$arr]['skuId']               = intval($row['skuId']);
                $v[$arr]['skuName']             = '';
            }
            $info['data']['entries']            = $v;
            $info['data']['accId']              = (float)$data['accId'];
            $accounts = $this->data_model->get_account_info('and (iid='.$id.') order by id');
            foreach ($accounts as $arr=>$row) {
                $s[$arr]['orderId']           = intval($id);
                $s[$arr]['billNo']              = $row['billNo'];
                $s[$arr]['buId']                = intval($row['buId']);
                $s[$arr]['billType']            = $row['billType'];
                $s[$arr]['transType']           = $row['transType'];
                $s[$arr]['transTypeName']       = $row['transTypeName'];
                $s[$arr]['billDate']            = $row['billDate'];
                $s[$arr]['accId']               = intval($row['accId']);
                $s[$arr]['account']             = $row['accountNumber'].''.$row['accountName'];
                $s[$arr]['payment']             = (float)abs($row['payment']);
                $s[$arr]['wayId']               = (float)$row['wayId'];
                $s[$arr]['way']                 = $row['categoryName'];
                $s[$arr]['settlement']          = $row['settlement'];
            }
            $info['data']['accounts']           = $s;
            die(json_encode($info));
        }
        str_alert(-1,'单据不存在、或者已删除');
    }

    function getLinkman($linkMans)
    {
        if (strlen($linkMans) <= 2)
        {
            return null;
        }
        $list = (array)json_decode($linkMans,true);
        $row = null;
        foreach ($list as $arr1=>$row1)
        {
            if ($row1['linkFirst'] == 1)
            {
                $row = $row1;
                break;
            }
        }
        if (empty($row))
        {
            $row = $list[0];
        }
        return $row;
    }

    //打印
    public function toPdf() {
        //Array ( [sidx] => [sord] => asc [op] => 2 [matchCon] => [transType] => 150501 [beginDate] => 2017-09-01 [endDate] => 2017-09-20 [marginLeft] => ) 1
        $this->common_model->checkpurview(85);
        $id   = intval($this->input->get('id',TRUE));
        if(empty($id))
        {
            //list
            $sidx = str_enhtml($this->input->get_post('sidx',TRUE));
            $sord = str_enhtml($this->input->get_post('sord',TRUE));
            $transType = intval($this->input->get_post('transType',TRUE));
            if(empty($transType)){$transType='150501';}
            $matchCon  = str_enhtml($this->input->get_post('matchCon',TRUE));
            $beginDate = str_enhtml($this->input->get_post('beginDate',TRUE));
            $endDate   = str_enhtml($this->input->get_post('endDate',TRUE));
            $order = $sidx ? $sidx.' '.$sord :' a.id desc';
            $where = ' and a.billType="PUR"';
            $where .= $transType>0  ? ' and a.transType='.$transType : '';
            $where .= $matchCon  ? ' and (b.name like "%'.$matchCon.'%" or description like "%'.$matchCon.'%" or billNo like "%'.$matchCon.'%")' : '';
            $where .= $beginDate ? ' and a.billDate>="'.$beginDate.'"' : '';
            $where .= $endDate ? ' and a.billDate<="'.$endDate.'"' : '';
            $where1 = ' and a.billType="PUR"';
            $where1 .= $transType>0  ? ' and a.transType='.$transType : '';
            $where1 .= $beginDate ? ' and a.billDate>="'.$beginDate.'"' : '';
            $where1 .= $endDate ? ' and a.billDate<="'.$endDate.'"' : '';
            $data['list1'] = $this->data_model->get_order($where.' order by '.$order.'');
            $data['list2'] = $this->data_model->get_order_info($where1.' order by a.billDate');
            if (count($data)>0) {
                ob_start();
                $this->load->view('scm/invPo/listToPdf',$data);
                $content = ob_get_clean();
                require_once('./application/libraries/html2pdf/html2pdf.php');
                try {
                    $html2pdf = new HTML2PDF('L', 'A4', 'tr');
                    $html2pdf->setDefaultFont('javiergb');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($content, '');
                    $html2pdf->Output('invPo_'.date('ymdHis').'.pdf','I');
                }catch(HTML2PDF_exception $e) {
                    echo $e;
                    exit;
                }
            }
            else {
                str_alert(-1,'单据不存在、或者已删除');
            }
        }
        else
        {
            //item
            $data = $this->data_model->get_order('and (a.id='.$id.') and billType="PUR"',1);
            //exit(print_r($data));
            if (count($data)>0) {
                $storage   = $this->mysql_model->get_row(STORAGE,"(disable=0 and id=$data[locationId])");
                if(!empty($storage))
                {
                    $data['storageName']=$storage[name];
                    $data['storageAddress']=$storage[address];
                    $data['storageManager']=$storage[manager];
                    $data['storagePhone']=$storage[phone];
                }
                $linkMan = $this->getLinkman($data['linkMans']);
                if (!empty($linkMan))
                {
                    $data['salesName'] = $linkMan['linkName'];
                    $data['salesMobile'] = $linkMan['linkMobile'];
                    $data['salesEmail'] = $linkMan['linkIm'];
                    $data['salesPhone'] = $linkMan['linkPhone'];
                    $data['salesAddress'] = $linkMan['province'] . $linkMan['city'] . $linkMan['county'] . $linkMan['address'];
                }
                //exit("test");
                $list = $this->data_model->get_order_info('and (iid='.$id.') order by id');

                //exit(print_r($list));

                $data['num']    = 8;
                $data['system'] = $this->common_model->get_option('system');
                $data['countpage']  = ceil(count($list)/$data['num']);
                foreach($list as $arr=>$row) {
                    $data['list'][] = array(
                        'i'=>$arr + 1,
                        'goods'=>$row['invName'],
                        'invSpec'=>$row['invSpec'],
                        'unitName'=>$row['mainUnit'],
                        'qty'=>abs($row['qty']),
                        'price'=>$row['price'],
                        'discountRate'=>$row['discountRate']>0?$row['discountRate']:'',
                        'deduction'=>$row['deduction']>0?$row['deduction']:'',
                        'amount'=>$row['amount'],
                        'currencyCode'=>$row['currencyCode'],
                        'locationName'=>$row['locationName']
                    );
                    if ($row['discountRate'] > 0) {
                        $data['haveItemDisrate'] = true;
                    }
                }
                ob_start();
                //exit('orderType:'.$data['orderType']);

                $data['paymentName']    = $this->mysql_model->get_row(CATEGORY,"(id=$data[paymentMethod])",'name');
                $data['shippingName']    = $this->mysql_model->get_row(CATEGORY,"(id=$data[shippingMethod])",'name');
                if(empty($data['paymentName']))
                {
                    $data['paymentName']="";
                }
                if(empty($data['shippingName']))
                {
                    $data['shippingName']="";
                }
                if($data['currency']==2)
                {
                    $this->load->view('scm/invPo/toEpdf',$data);
                }
                else
                {
                    $this->load->view('scm/invPo/toPdf',$data);
                }
                $content = ob_get_clean();

                require_once('./application/libraries/html2pdf/html2pdf.php');
                try {
                    $html2pdf = new HTML2PDF('P', 'A4', 'tr');
                    $html2pdf->setDefaultFont('javiergb');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($content, '');
                    $html2pdf->Output('invPo_'.date('ymdHis').'.pdf','I');
                }catch(HTML2PDF_exception $e) {
                    echo $e;
                    exit;
                }
            }
            else {
                str_alert(-1,'单据不存在、或者已删除');
            }
        }
    }

    //购购单删除
    public function delete() {
        $this->common_model->checkpurview(4);
        $id   = intval($this->input->get('id',TRUE));
        $data = $this->mysql_model->get_row(ORDER,'(id='.$id.') and billType="PUR"');
        if (count($data)>0) {
            $data['checked'] >0 && str_alert(-1,'已审核的不可删除');
            $info['isDelete'] = 1;
            $this->db->trans_begin();
            $this->mysql_model->update(ORDER,$info,'(id='.$id.')');
            $this->mysql_model->update(ORDER_INFO,$info,'(iid='.$id.')');
            $this->mysql_model->update(ACCOUNT_INFO,$info,'(iid='.$id.')');
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                str_alert(-1,'删除失败');
            } else {
                $this->db->trans_commit();
                $this->common_model->logs('删除采购订单 单据编号：'.$data['billNo']);
                str_alert(200,'success');
            }
        }
        str_alert(-1,'单据不存在、或者已删除');
    }

    //批量审核
    public function batchCheckInvPo() {
        $this->common_model->checkpurview(86);
        $id   = str_enhtml($this->input->post('id',TRUE));
        $data = $this->mysql_model->get_results(ORDER,'(id in('.$id.')) and billType="PUR" and checked=0 and (isDelete=0)');
        if (count($data)>0) {
            $info['checked']   = 1;
            $info['checkName'] = $this->jxcsys['name'];
            $this->db->trans_begin();
            $this->mysql_model->update(ORDER,$info,'(id in('.$id.'))');
            $this->mysql_model->update(ORDER_INFO,$info,'(id in('.$id.'))');
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                str_alert(-1,'审核失败');
            } else {
                $this->db->trans_commit();
                $billno = array_column($data,'billNo');
                $billno = join(',',$billno);
                $this->common_model->logs('采购订单订单编号：'.$billno.'的单据已被审核！');
                str_alert(200,'订单编号：'.$billno.'的单据已被审核！');
            }
        }
        str_alert(-1,'所选的单据都已被审核，请选择未审核的单据进行审核！');
    }

    //批量反审核
    public function rsBatchCheckInvPo() {
        $this->common_model->checkpurview(87);
        $id   = str_enhtml($this->input->post('id',TRUE));
        $data = $this->mysql_model->get_results(ORDER,'(id in('.$id.')) and billType="PUR" and checked=1 and (isDelete=0)');
        if (count($data)>0) {
            $info['checked']   = 0;
            $info['checkName'] = '';
            $this->db->trans_begin();
            $this->mysql_model->update(ORDER,$info,'(id in('.$id.'))');
            $this->mysql_model->update(ORDER_INFO,$info,'(id in('.$id.'))');
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                str_alert(-1,'反审核失败');
            } else {
                $this->db->trans_commit();
                $billno = array_column($data,'billNo','id');
                $billno = join(',',$billno);
                $this->common_model->logs('采购订单单号：'.$billno.'的单据已被反审核！');
                str_alert(200,'订单编号：'.$billno.'的单据已被反审核！');
            }
        }
        str_alert(-1,'所选的订单都是未审核，请选择已审核的订单进行反审核！');
    }

    //单个审核
    public function checkInvPo() {
        $this->common_model->checkpurview(86);
        $jsondata = $this->input->post('postData',TRUE);
        $data = (array)json_decode($jsondata, true);
        $id   = intval($data["id"]);
        if($id<=0)
        {
            str_alert(-1,'数据还未保存');
        }
        if (strlen($jsondata)>0) {
            $data = $this->validform($data);
            $data['checked']         = 1;
            $data['checkName']       = $this->jxcsys['name'];
            $info = elements(array(
                'checked',
                'checkName',
                'billType',
                'transType',
                'transTypeName',
                'buId',
                'billDate',
                'description',
                'totalQty',
                'amount',
                'arrears',
                'rpAmount',
                //'currency',
                'totalAmount',
                'hxStateCode',
                'totalArrears',
                'disRate',
                'disAmount',
                'uid',
                'userName',
                'accId',
                'modifyTime'
            ),$data);
            $this->db->trans_begin();
            $this->mysql_model->update(ORDER,$info,'(id='.$id.')');
            $this->order_info($id,$data);
            $this->account_info($id,$data);
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                str_alert(-1,'SQL错误');
            } else {
                $this->db->trans_commit();
                $this->common_model->logs('采购单 单据编号：'.$data['billNo'].'的单据已被审核！');
                str_alert(200,'success',array('id'=>$id));
            }
        }
        str_alert(-1,'提交的数据不能为空');
    }


    //单个反审核
    public function revsCheckInvPo() {
        $this->common_model->checkpurview(87);
        $data = $this->input->post('postData',TRUE);
        if (strlen($data)>0) {
            $data = (array)json_decode($data, true);
            $id   = intval($data['id']);
            $data = $this->validform($data);
            $data['checked']         = 0;
            $data['checkName']       = '';
            $info = elements(array(
                'checked',
                'checkName',
                'billType',
                'transType',
                'transTypeName',
                'buId',
                'billDate',
                'description',
                'totalQty',
                'amount',
                'arrears',
                'rpAmount',
                //'currency',
                'totalAmount',
                'hxStateCode',
                'totalArrears',
                'disRate',
                'disAmount',
                'uid',
                'userName',
                'accId',
                'modifyTime'
            ),$data);
            $this->db->trans_begin();
            $this->mysql_model->update(ORDER,$info,'(id='.$id.')');
            $this->order_info($id,$data);
            $this->account_info($id,$data);
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                str_alert(-1,'SQL错误');
            } else {
                $this->db->trans_commit();
                $this->common_model->logs('采购单 单据编号：'.$data['billNo'].'的单据已被反审核！');
                str_alert(200,'success',array('id'=>$id));
            }
        }
        str_alert(-1,'提交的数据不能为空');
    }

    //公共验证
    private function validform($data) {
        //(float)$data['arrears'] < 0 || !is_numeric($data['arrears']) && str_alert(-1,'本次欠款要为数字，请输入有效数字！');
        //(float)$data['disRate'] < 0 || !is_numeric($data['disRate']) && str_alert(-1,'折扣率要为数字，请输入有效数字！');
        //(float)$data['rpAmount'] < 0 || !is_numeric($data['rpAmount']) && str_alert(-1,'本次收款要为数字，请输入有效数字！');
        //(float)$data['amount'] < (float)$data['rpAmount']  && str_alert(-1,'本次付款不能大于折后金额！');
        //(float)$data['amount'] < (float)$data['disAmount'] && str_alert(-1,'折扣额不能大于合计金额！');

        if (isset($data['id'])&&intval($data['id'])>0) {
            $data['id'] = intval($data['id']);
            $order = $this->mysql_model->get_row(ORDER,'(id='.$data['id'].') and billType="PUR" and isDelete=0');  //修改的时候判断
            count($order)<1 && str_alert(-1,'单据不存在、或者已删除');
            //jason.xie 暂时删除
            //$invoice['checked']>0 && str_alert(-1,'审核后不可修改');
            $data['billNo'] =  $order['billNo'];
        } else {
            $data['billNo']      = str_no('PO');    //修改的时候屏蔽
        }

        $data['billType']        = 'PUR';
        $data['transType']       = intval($data['transType']);
        $data['transTypeName']   = $data['transType']==150501 ? '采购' : '退货';
        $data['buId']            = intval($data['buId']);
        $data['billDate']        = $data['date'];
        $data['description']     = $data['description'];
        $data['totalQty']        = (float)$data['totalQty'];
        if ($data['transType']==150501) {
            $data['amount']      = abs($data['amount']);
            $data['arrears']     = abs($data['arrears']);
            $data['rpAmount']    = abs($data['rpAmount']);
            $data['totalAmount'] = abs($data['totalAmount']);
        } else {
            $data['amount']      = -abs($data['amount']);
            $data['arrears']     = -abs($data['arrears']);
            $data['rpAmount']    = -abs($data['rpAmount']);
            $data['totalAmount'] = -abs($data['totalAmount']);
        }
        //exit(print_r($this->jxcsys));
        $data['hxStateCode']     = $data['rpAmount']==$data['amount'] ? 2 : ($data['rpAmount']>0 ? 1 : 0);
        $data['totalArrears']    = (float)$data['totalArrears'];
        $data['disRate']         = (float)$data['disRate'];
        $data['disAmount']       = (float)$data['disAmount'];
        $data['uid']             = $this->jxcsys['uid'];
        $data['userName']        = $this->jxcsys['name'];
        $data['accId']           = (float)$data['accId'];

        $data['modifyTime']      = date('Y-m-d H:i:s');

        //选择了结算账户 需要验证
        if (isset($data['accounts']) && count($data['accounts'])>0) {
            foreach ($data['accounts'] as $arr=>$row) {
                (float)$row['payment'] < 0 || !is_numeric($row['payment']) && str_alert(-1,'结算金额要为数字，请输入有效数字！');
            }
        }

        //供应商验证
        $this->mysql_model->get_count(CONTACT,'(id='.intval($data['buId']).')')<1 && str_alert(-1,'采购单位不存在');

        //商品录入验证
        if (is_array($data['entries'])) {
            $system    = $this->common_model->get_option('system');
            if ($system['requiredCheckStore']==1) {  //开启检查时判断
                $item = array();
                //exit(print_r($data['entries']));
                foreach($data['entries'] as $k=>$v){
                    !isset($v['invId']) && str_alert(-1,'参数错误');
                    !isset($v['locationId']) && str_alert(-1,'参数错误');
                    if(!isset($item[$v['invId'].'-'.$v['locationId']])){
                        $item[$v['invId'].'-'.$v['locationId']] = $v;
                    }else{
                        $item[$v['invId'].'-'.$v['locationId']]['qty'] += $v['qty'];        //同一仓库 同一商品 数量累加
                    }
                }
                $inventory = $this->data_model->get_invoice_info_inventory();
            } else {
                $item = $data['entries'];
            }
            $storage   = array_column($this->mysql_model->get_results(STORAGE,'(disable=0)'),'id');
            //exit(print_r($item));

            foreach ($item as $arr=>$row) {
                !isset($row['invId']) && str_alert(-1,'参数错误');
                !isset($row['locationId']) && str_alert(-1,'参数错误');
                (float)$row['qty'] < 0 || !is_numeric($row['qty']) && str_alert(-1,'商品数量要为数字，请输入有效数字！');
                (float)$row['price'] < 0 || !is_numeric($row['price']) && str_alert(-1,'商品销售单价要为数字，请输入有效数字！');
                (float)$row['discountRate'] < 0 || !is_numeric($row['discountRate']) && str_alert(-1,'折扣率要为数字，请输入有效数字！');
                intval($row['locationId']) < 1 && str_alert(-1,'请选择相应的仓库！');
                !in_array(intval($row['locationId']),$storage) && str_alert(-1,$row['locationName'].'不存在或不可用！');
                //库存判断
                if ($system['requiredCheckStore']==1) {
                    if (intval($data['transType'])==150502) {                        //退货才验证
                        if (isset($inventory[$row['invId']][$row['locationId']])) {
                            $inventory[$row['invId']][$row['locationId']] < (float)$row['qty'] && str_alert(-1,$row['locationName'].$row['invName'].'商品库存不足！');
                        } else {
                            str_alert(-1,$row['invName'].'库存不足！');
                        }
                    }
                }
            }
        } else {
            str_alert(-1,'提交的是空数据');
        }
        return $data;
    }


    //组装数据
    private function order_info($iid,$data) {
        if (is_array($data['entries'])) {
            foreach ($data['entries'] as $arr=>$row) {
                $v[$arr]['iid']           = intval($iid);
                $v[$arr]['billNo']        = $data['billNo'];
                $v[$arr]['buId']          = $data['buId'];
                $v[$arr]['billDate']      = $data['billDate'];
                $v[$arr]['billType']      = $data['billType'];
                $v[$arr]['transType']     = $data['transType'];
                $v[$arr]['transTypeName'] = $data['transTypeName'];
                $v[$arr]['invId']         = intval($row['invId']);
                $v[$arr]['skuId']         = intval($row['skuId']);
                $v[$arr]['unitId']        = intval($row['unitId']);
                $v[$arr]['locationId']    = intval($row['locationId']);
                if ($data['transType']==150501) {
                    $v[$arr]['qty']       = abs($row['qty']);
                    $v[$arr]['amount']    = abs($row['amount']);
                } else {
                    $v[$arr]['qty']       = -abs($row['qty']);
                    $v[$arr]['amount']    = -abs($row['amount']);
                }
                $v[$arr]['price']         = abs($row['price']);
                $v[$arr]['discountRate']  = $row['discountRate'];
                $v[$arr]['deduction']     = $row['deduction'];
                $v[$arr]['description']   = $row['description'];

            }
            if (isset($v)) {
                if (isset($data['id']) && $data['id']>0) {                    //修改的时候
                    $this->mysql_model->delete(ORDER_INFO,'(iid='.$iid.')');
                }
                $this->mysql_model->insert(ORDER_INFO,$v);
            }
        }
    }

    //组装数据
    private function account_info($iid,$data) {
        if (isset($data['accounts']) && count($data['accounts'])>0) {
            foreach ($data['accounts'] as $arr=>$row) {
                if (isset($row['accId']) && intval($row['accId'])>0) {
                    $v[$arr]['iid']           = intval($iid);
                    $v[$arr]['billNo']        = $data['billNo'];
                    $v[$arr]['buId']          = $data['buId'];
                    $v[$arr]['billType']      = $data['billType'];
                    $v[$arr]['transType']     = $data['transType'];
                    $v[$arr]['transTypeName'] = $data['transType']==150501 ? '普通采购' : '采购退回';
                    $v[$arr]['payment']       = $data['transType']==150501 ? -$row['payment'] : $row['payment'];
                    $v[$arr]['billDate']      = $data['billDate'];
                    $v[$arr]['accId']         = $row['accId'];
                    $v[$arr]['wayId']         = $row['wayId'];
                    $v[$arr]['settlement']    = $row['settlement'];
                }
            }
            if (isset($v)) {
                if (isset($data['id']) && $data['id']>0) {                      //修改的时候
                    $this->mysql_model->delete(ACCOUNT_INFO,'(iid='.$iid.')');
                }
                if(false==$this->mysql_model->insert(ACCOUNT_INFO,$v))
                {
                    exit("account_info更新错误");
                }
            }
        }
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
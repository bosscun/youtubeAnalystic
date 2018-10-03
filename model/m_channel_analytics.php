<?php
include 'config.php';
class m_channel_analytics extends ConnectionDB
{
    public function UpdateChannelAnalytics($channelID,$Report,$MonthlyReport,$RevenueReport,$CreateDate)
    {
        $sql = "UPDATE `tbl_analystic` SET `Report`= '$Report',`MonthlyReport`='$MonthlyReport',`RevenueReport`='$RevenueReport', `CreateDate`= '$CreateDate' WHERE `ChannelID` = '$channelID'";
        $row = parent::query($sql);
        return $row;
    }

    public function InsertChannelAnalytics($ChannelID, $Report,$MonthlyReport,$RevenueReport, $CreateDate){
        $sql =" INSERT INTO `tbl_analystic`(`ChannelID`, `Report`,`MonthlyReport`,`RevenueReport`,`CreateDate`) VALUES ('$ChannelID', '$Report', '$MonthlyReport','$RevenueReport','$CreateDate')";
        $row = parent::query($sql);
        return $row;
    }

    public function GetAllChannel()
    {
        $sql = "SELECT * FROM `tbl_channelinfo` WHERE Status =1";
        $rows = ConnectionDB::fetch($sql);
        return $rows;
    }
    public function CheckChannelAnalytics($ChannelID)
    {
        $sql = "SELECT * FROM `tbl_analystic` WHERE `ChannelID`='$ChannelID'";
        $row = ConnectionDB::fetch($sql);
        return $row;
    }
}

?>
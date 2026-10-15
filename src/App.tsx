import React, { useState } from 'react';
import {
  Layers,
  ShieldCheck,
  Server,
  Database,
  UserCheck,
  Lock,
  FileCode,
  CheckCircle,
  Copy,
  ChevronRight,
  TrendingUp,
  Award,
  Users,
  Wallet,
  ArrowRight,
  Eye,
  AlertTriangle,
  FolderTree,
  Terminal,
  KeyRound,
  FileText,
  CreditCard,
  History,
  CheckSquare,
  AlertCircle,
  UploadCloud,
  Check,
  X,
  Clock,
  ArrowUpCircle,
  Network,
  GitBranch,
  Search,
  ZoomIn,
  ZoomOut,
  RotateCcw,
  UserPlus,
  HelpCircle,
  ShieldAlert,
  DollarSign,
  Headphones,
  MessageSquare,
  Send,
  Bell,
  Sliders,
  UserX,
} from 'lucide-react';

interface PackagePlan {
  level: number;
  name: string;
  price: number;
  teamSize: number;
  direct: string;
  levelInc: string;
  sponsorInc: string;
  badge: string;
}

const PLAN_PACKAGES: PackagePlan[] = [
  { level: 1, name: 'Level 1', price: 1200, teamSize: 2, direct: '100%', levelInc: '-', sponsorInc: '-', badge: 'Joining Entry' },
  { level: 2, name: 'Level 2', price: 2000, teamSize: 4, direct: '-', levelInc: '50%', sponsorInc: '50%', badge: 'Upgrade L2' },
  { level: 3, name: 'Level 3', price: 3000, teamSize: 8, direct: '-', levelInc: '50%', sponsorInc: '50%', badge: 'Upgrade L3' },
  { level: 4, name: 'Level 4', price: 8000, teamSize: 16, direct: '-', levelInc: '50%', sponsorInc: '50%', badge: 'Upgrade L4' },
  { level: 5, name: 'Level 5', price: 40000, teamSize: 32, direct: '-', levelInc: '50%', sponsorInc: '50%', badge: 'Upgrade L5' },
  { level: 6, name: 'Level 6', price: 200000, teamSize: 64, direct: '-', levelInc: '50%', sponsorInc: '50%', badge: 'Upgrade L6' },
  { level: 7, name: 'Level 7', price: 500000, teamSize: 128, direct: '-', levelInc: '50%', sponsorInc: '50%', badge: 'Top Level L7' },
];

interface TreeNode {
  id: number;
  username: string;
  fullName: string;
  mobile: string;
  level: number;
  status: 'active' | 'pending';
  position?: 'left' | 'right';
  parentId?: number;
  sponsorId?: number;
  sponsorUsername?: string;
  joinDate: string;
  left?: TreeNode | null;
  right?: TreeNode | null;
}

export default function App() {
  const [activeTab, setActiveTab] = useState<'preview' | 'files' | 'database' | 'setup'>('preview');
  const [previewView, setPreviewView] = useState<
    'admin_members' | 'admin_kyc' | 'admin_settings' | 'user_support' | 'admin_support' | 'user_notifications' | 'wallet' | 'withdraw' | 'transactions' | 'admin_withdrawals' | 'income_user' | 'income_admin' | 'binary_tree' | 'team' | 'sponsor' | 'admin_tree' | 'packages' | 'upgrade_history' | 'admin_upgrades' | 'admin_packages' | 'user_dash' | 'register' | 'login' | 'home'
  >('admin_members');

  const [selectedFile, setSelectedFile] = useState<string>('admin/members.php');
  const [copiedNotification, setCopiedNotification] = useState<string | null>(null);

  // Part 6 Wallet & Withdrawal Simulator State
  const [simWallet, setSimWallet] = useState({
    balance: 4700,
    totalCredit: 6200,
    totalDebit: 1500,
    pendingWithdrawal: 1000,
    availableForWithdrawal: 4700,
    totalWithdrawn: 500,
    directIncome: 2400,
    levelIncome: 1000,
    sponsorIncome: 2800,
  });

  const [simWithdrawals, setSimWithdrawals] = useState([
    {
      id: 1,
      refNo: 'WTH-20261014-A82B91',
      username: 'rohit99',
      fullName: 'Rohit Verma',
      mobile: '9876543210',
      level: 2,
      kycStatus: 'approved',
      amount: 1000,
      tds: 50,
      adminFee: 50,
      netAmount: 900,
      paymentMode: 'Bank Transfer',
      bankDetails: 'HDFC Bank - A/C 9812345678 - HDFC0001234',
      status: 'pending' as 'pending' | 'approved' | 'rejected',
      date: '14 Oct 2026, 05:10 PM',
      remarks: '',
      utr: '',
    },
    {
      id: 2,
      refNo: 'WTH-20261012-C19F44',
      username: 'rohit99',
      fullName: 'Rohit Verma',
      mobile: '9876543210',
      level: 2,
      kycStatus: 'approved',
      amount: 500,
      tds: 25,
      adminFee: 25,
      netAmount: 450,
      paymentMode: 'UPI',
      bankDetails: 'rohit@okhdfcbank',
      status: 'approved' as 'pending' | 'approved' | 'rejected',
      date: '12 Oct 2026, 02:45 PM',
      remarks: 'Disbursed via UPI payment gateway.',
      utr: 'UPI/229104882190',
    },
  ]);

  const [simTransactions, setSimTransactions] = useState([
    {
      id: 105,
      refNo: 'WTH-20261014-A82B91',
      date: '14 Oct 2026, 05:10 PM',
      type: 'debit',
      category: 'withdrawal',
      amount: 1000,
      balanceBefore: 5700,
      balanceAfter: 4700,
      status: 'pending',
      desc: 'Withdrawal request of ₹1,000 (Net: ₹900 via Bank Transfer)',
    },
    {
      id: 104,
      refNo: 'TXN-LVL-20261014-991A42',
      date: '14 Oct 2026, 03:20 PM',
      type: 'credit',
      category: 'level_income',
      amount: 1000,
      balanceBefore: 4700,
      balanceAfter: 5700,
      status: 'completed',
      desc: '50% Level 2 Income from @sunita_k (Package ₹2,000)',
    },
    {
      id: 103,
      refNo: 'TXN-SPO-20261013-882B19',
      date: '13 Oct 2026, 11:45 AM',
      type: 'credit',
      category: 'sponsor_income',
      amount: 1500,
      balanceBefore: 3200,
      balanceAfter: 4700,
      status: 'completed',
      desc: '50% Sponsor Income from direct member @amit_s (Upgraded to Level 3)',
    },
    {
      id: 102,
      refNo: 'WTH-20261012-C19F44',
      date: '12 Oct 2026, 02:45 PM',
      type: 'debit',
      category: 'withdrawal',
      amount: 500,
      balanceBefore: 3700,
      balanceAfter: 3200,
      status: 'completed',
      desc: 'Withdrawal request of ₹500 (Net: ₹450 via UPI) [APPROVED: Disbursed]',
    },
    {
      id: 101,
      refNo: 'TXN-DIR-20261010-771C02',
      date: '10 Oct 2026, 10:00 AM',
      type: 'credit',
      category: 'direct_income',
      amount: 1200,
      balanceBefore: 2500,
      balanceAfter: 3700,
      status: 'completed',
      desc: '100% Direct Income from user @priya_sharma (Level 1 Activation)',
    },
  ]);

  const [txFilter, setTxFilter] = useState<'all' | 'income' | 'withdrawal' | 'refund'>('all');
  const [withdrawAmountInput, setWithdrawAmountInput] = useState<number>(500);
  const [withdrawModeInput, setWithdrawModeInput] = useState<'Bank Transfer' | 'UPI'>('Bank Transfer');
  const [withdrawAlert, setWithdrawAlert] = useState<{ type: 'success' | 'error'; msg: string } | null>(null);
  const [adminActionAlert, setAdminActionAlert] = useState<{ type: 'success' | 'error'; msg: string } | null>(null);
  const [selectedWithdrawalForAction, setSelectedWithdrawalForAction] = useState<number | null>(null);
  const [actionType, setActionType] = useState<'approve' | 'reject' | null>(null);
  const [adminUtrInput, setAdminUtrInput] = useState<string>('');
  const [adminRemarksInput, setAdminRemarksInput] = useState<string>('');

  // Part 7: Members Management Simulator State
  const [simMembers, setSimMembers] = useState([
    { id: 1, username: 'admin', fullName: 'System Administrator', email: 'admin@ssdream.com', mobile: '9999999999', level: 7, walletBalance: 24500, totalIncome: 58000, kyc: 'approved', status: 'active', sponsor: 'Direct', leg: '', joinDate: '01 Oct 2026' },
    { id: 2, username: 'ramesh01', fullName: 'Ramesh Sharma', email: 'ramesh@example.com', mobile: '9876543210', level: 2, walletBalance: 4700, totalIncome: 6200, kyc: 'approved', status: 'active', sponsor: 'admin', leg: 'left', joinDate: '05 Oct 2026' },
    { id: 3, username: 'suresh02', fullName: 'Suresh Kumar', email: 'suresh@example.com', mobile: '9876543211', level: 1, walletBalance: 1200, totalIncome: 1200, kyc: 'pending', status: 'active', sponsor: 'ramesh01', leg: 'left', joinDate: '08 Oct 2026' },
    { id: 4, username: 'anita03', fullName: 'Anita Singh', email: 'anita@example.com', mobile: '9876543212', level: 1, walletBalance: 0, totalIncome: 0, kyc: 'rejected', status: 'blocked', sponsor: 'ramesh01', leg: 'right', joinDate: '10 Oct 2026' },
    { id: 5, username: 'vikram04', fullName: 'Vikram Malhotra', email: 'vikram@example.com', mobile: '9876543213', level: 3, walletBalance: 8200, totalIncome: 15400, kyc: 'approved', status: 'active', sponsor: 'suresh02', leg: 'left', joinDate: '12 Oct 2026' },
  ]);
  const [memberSearchQuery, setMemberSearchQuery] = useState('');
  const [memberStatusFilter, setMemberStatusFilter] = useState<'all' | 'active' | 'blocked' | 'pending'>('all');
  const [memberLevelFilter, setMemberLevelFilter] = useState<string>('');
  const [memberActionAlert, setMemberActionAlert] = useState<string | null>(null);

  // Part 7: KYC Verification Simulator State
  const [simKycRequests, setSimKycRequests] = useState([
    { id: 1, username: 'suresh02', fullName: 'Suresh Kumar', mobile: '9876543211', docType: 'Aadhar Card', docNumber: '9823-4412-9901', bankName: 'State Bank of India', accNo: '31928374615', ifsc: 'SBIN0004921', upi: 'suresh@sbi', status: 'pending' as 'pending' | 'approved' | 'rejected', date: '14 Oct 2026, 04:30 PM', adminNotes: '' },
    { id: 2, username: 'anita03', fullName: 'Anita Singh', mobile: '9876543212', docType: 'PAN Card', docNumber: 'ABCDE1234F', bankName: 'Punjab National Bank', accNo: '78234120912', ifsc: 'PUNB0019234', upi: 'anita@pnb', status: 'rejected' as 'pending' | 'approved' | 'rejected', date: '12 Oct 2026, 02:15 PM', adminNotes: 'Uploaded document image is blurry and account holder name does not match member profile.' },
    { id: 3, username: 'ramesh01', fullName: 'Ramesh Sharma', mobile: '9876543210', docType: 'Aadhar Card', docNumber: '4412-8823-1190', bankName: 'HDFC Bank', accNo: '9812345678', ifsc: 'HDFC0001234', upi: 'ramesh@okhdfcbank', status: 'approved' as 'pending' | 'approved' | 'rejected', date: '06 Oct 2026, 10:45 AM', adminNotes: 'Verified with official portal' },
  ]);
  const [kycTab, setKycTab] = useState<'all' | 'pending' | 'approved' | 'rejected'>('pending');
  const [kycActionAlert, setKycActionAlert] = useState<string | null>(null);
  const [kycRejectNote, setKycRejectNote] = useState<string>('');
  const [rejectingKycId, setRejectingKycId] = useState<number | null>(null);

  // Part 7: System Settings Simulator State
  const [simSettings, setSimSettings] = useState({
    siteName: 'Social Service Dream',
    currency: '₹',
    supportEmail: 'support@ssdream.com',
    supportPhone: '+91 98765 43210',
    whatsapp: '+91 98765 43210',
    minWithdrawal: 500,
    tdsPercent: 5,
    adminChargePercent: 10,
    companyBankName: 'State Bank of India',
    companyAccountHolder: 'Social Service Dream Welfare Foundation',
    companyAccountNumber: '39820100049281',
    companyIfscCode: 'SBIN0001234',
    companyUpiId: 'ssdream@sbi',
    noticeText: 'Welcome to SS Dream! Ensure your KYC is completed to enable instant withdrawals. Direct income 100%, Level & Sponsor income 50%.',
    noticeStatus: 'active',
  });
  const [settingsSavedAlert, setSettingsSavedAlert] = useState<string | null>(null);

  // Part 7: Support Tickets Simulator State
  const [simTickets, setSimTickets] = useState([
    {
      id: 1,
      ticketNo: 'TKT-20261014-9B21',
      username: 'ramesh01',
      fullName: 'Ramesh Sharma',
      subject: 'Inquiry regarding Level 3 Package Upgrade approval',
      category: 'Level Upgrade / Package',
      priority: 'medium',
      status: 'in_progress' as 'open' | 'in_progress' | 'resolved' | 'closed',
      updatedAt: '14 Oct 2026, 03:45 PM',
      messages: [
        { id: 1, sender: 'user', name: 'Ramesh Sharma', text: 'Hello SS Dream team, I have submitted the payment slip for Level 3 upgrade. Please check and activate.', time: '03:20 PM' },
        { id: 2, sender: 'admin', name: 'Support Admin', text: 'Hello Ramesh, we have verified your payment reference with our bank ledger. Your upgrade is being confirmed.', time: '03:45 PM' }
      ]
    },
    {
      id: 2,
      ticketNo: 'TKT-20261012-7F10',
      username: 'suresh02',
      fullName: 'Suresh Kumar',
      subject: 'When will my KYC verification be completed?',
      category: 'KYC Verification',
      priority: 'low',
      status: 'open' as 'open' | 'in_progress' | 'resolved' | 'closed',
      updatedAt: '12 Oct 2026, 11:15 AM',
      messages: [
        { id: 1, sender: 'user', name: 'Suresh Kumar', text: 'I submitted my Aadhar card details yesterday. Kindly approve it soon so I can withdraw funds.', time: '11:15 AM' }
      ]
    }
  ]);
  const [selectedTicketThreadId, setSelectedTicketThreadId] = useState<number>(1);
  const [newTicketSubject, setNewTicketSubject] = useState('');
  const [newTicketCategory, setNewTicketCategory] = useState('Withdrawal / Payout');
  const [newTicketPriority, setNewTicketPriority] = useState('medium');
  const [newTicketMessage, setNewTicketMessage] = useState('');
  const [ticketReplyInput, setTicketReplyInput] = useState('');
  const [ticketActionAlert, setTicketActionAlert] = useState<string | null>(null);

  // Part 7: Notifications Simulator State
  const [simNotifications, setSimNotifications] = useState([
    { id: 1, title: 'KYC Review In Progress', message: 'Your submitted Aadhar documents are currently queued for admin compliance check.', isRead: false, time: '14 Oct 2026, 05:30 PM' },
    { id: 2, title: 'Withdrawal Request Received', message: 'Your withdrawal request for ₹1,000 (Net ₹900) has been placed under pending admin review.', isRead: false, time: '14 Oct 2026, 05:10 PM' },
    { id: 3, title: 'Direct Sponsor Bonus ₹1,200', message: 'Congratulations! Direct referral bonus of ₹1,200 credited from user @suresh02 joining.', isRead: true, time: '08 Oct 2026, 10:00 AM' },
    { id: 4, title: 'Welcome to Social Service Dream', message: 'Your account registration was successful. Start sharing your referral link to build your 2x2 team.', isRead: true, time: '05 Oct 2026, 09:00 AM' }
  ]);

  // Income Simulation State (Part 5)
  const [incomeTypeFilter, setIncomeTypeFilter] = useState<'all' | 'direct' | 'level' | 'sponsor'>('all');
  const [testSimulateResult, setTestSimulateResult] = useState<string | null>(null);

  // Upgrade History State
  const [historyFilter, setHistoryFilter] = useState<'all' | 'pending' | 'approved' | 'rejected'>('all');
  const [historyModalItem, setHistoryModalItem] = useState<{
    id: number;
    fromLevel: number;
    toLevel: number;
    packageName: string;
    amount: number;
    paymentMode: string;
    transactionRef: string;
    date: string;
    reviewedAt: string;
    status: 'pending' | 'approved' | 'rejected';
    remarks: string;
  } | null>(null);

  // Tree Zoom State
  const [treeZoom, setTreeZoom] = useState<number>(1);
  const [treeRootId, setTreeRootId] = useState<number>(1); // 1 = Ramesh Kumar
  const [unauthorizedAlert, setUnauthorizedAlert] = useState<string | null>(null);

  // Admin Search State
  const [adminSearchInput, setAdminSearchInput] = useState<string>('');
  const [adminInspectedId, setAdminInspectedId] = useState<number>(1);

  // Network Members Database (Simulated)
  const networkMembers: Record<number, TreeNode> = {
    1: {
      id: 1,
      username: 'ramesh99',
      fullName: 'Ramesh Kumar',
      mobile: '9876543210',
      level: 1,
      status: 'active',
      sponsorId: 0,
      sponsorUsername: 'None (Root)',
      joinDate: '10 Oct 2026',
    },
    2: {
      id: 2,
      username: 'priya22',
      fullName: 'Priya Sharma',
      mobile: '9812345678',
      level: 1,
      status: 'active',
      position: 'left',
      parentId: 1,
      sponsorId: 1,
      sponsorUsername: 'ramesh99',
      joinDate: '11 Oct 2026',
    },
    3: {
      id: 3,
      username: 'amit33',
      fullName: 'Amit Patel',
      mobile: '9823456789',
      level: 2,
      status: 'active',
      position: 'right',
      parentId: 1,
      sponsorId: 1,
      sponsorUsername: 'ramesh99',
      joinDate: '12 Oct 2026',
    },
    4: {
      id: 4,
      username: 'suresh44',
      fullName: 'Suresh Verma',
      mobile: '9834567890',
      level: 1,
      status: 'active',
      position: 'left',
      parentId: 2,
      sponsorId: 2,
      sponsorUsername: 'priya22',
      joinDate: '13 Oct 2026',
    },
    5: {
      id: 5,
      username: 'anita55',
      fullName: 'Anita Singh',
      mobile: '9845678901',
      level: 1,
      status: 'active',
      position: 'left',
      parentId: 3,
      sponsorId: 1,
      sponsorUsername: 'ramesh99',
      joinDate: '14 Oct 2026',
    },
  };

  // Helper to build tree structure for currently selected root
  const getSimulatedTree = (rootId: number) => {
    const root = networkMembers[rootId];
    if (!root) return null;

    // Children of root
    const children = Object.values(networkMembers).filter((m) => m.parentId === rootId);
    const leftChild = children.find((m) => m.position === 'left') || null;
    const rightChild = children.find((m) => m.position === 'right') || null;

    // Grandchildren
    let leftLeft = null;
    let leftRight = null;
    if (leftChild) {
      const gChildrenL = Object.values(networkMembers).filter((m) => m.parentId === leftChild.id);
      leftLeft = gChildrenL.find((m) => m.position === 'left') || null;
      leftRight = gChildrenL.find((m) => m.position === 'right') || null;
    }

    let rightLeft = null;
    let rightRight = null;
    if (rightChild) {
      const gChildrenR = Object.values(networkMembers).filter((m) => m.parentId === rightChild.id);
      rightLeft = gChildrenR.find((m) => m.position === 'left') || null;
      rightRight = gChildrenR.find((m) => m.position === 'right') || null;
    }

    return {
      root,
      left: leftChild ? { ...leftChild, left: leftLeft, right: leftRight } : null,
      right: rightChild ? { ...rightChild, left: rightLeft, right: rightRight } : null,
    };
  };

  // Drilldown security check
  const handleDrilldown = (targetId: number) => {
    // In our network, members 1, 2, 3, 4, 5 are all in Ramesh's downline
    if (networkMembers[targetId]) {
      setTreeRootId(targetId);
      setUnauthorizedAlert(null);
    } else {
      setUnauthorizedAlert('Unauthorized access: You can only view nodes within your own network.');
      setTimeout(() => setUnauthorizedAlert(null), 4000);
    }
  };

  const copyText = (text: string, label: string) => {
    navigator.clipboard.writeText(text);
    setCopiedNotification(label);
    setTimeout(() => setCopiedNotification(null), 2500);
  };

  const activeTreeData = getSimulatedTree(treeRootId);
  const adminTreeData = getSimulatedTree(adminInspectedId);

  return (
    <div className="min-h-screen bg-slate-900 text-slate-100 flex flex-col font-sans">
      {/* Top Banner Navigation */}
      <header className="border-b border-slate-800 bg-slate-950/80 backdrop-blur sticky top-0 z-50 px-4 py-3">
        <div className="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <div className="bg-gradient-to-r from-sky-500 to-blue-600 p-2 rounded-xl text-white shadow-lg shadow-sky-500/20">
              <GitBranch className="w-6 h-6" />
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h1 className="font-bold text-lg text-white tracking-wide">Social Service Dream</h1>
                <span className="bg-amber-500/10 text-amber-400 text-xs px-2.5 py-0.5 rounded-full font-semibold border border-amber-500/20">
                  Part 7: Member Management, KYC Verification, Settings &amp; Support Helpdesk
                </span>
              </div>
              <p className="text-xs text-slate-400">
                XAMPP Root: <code className="text-sky-300 font-mono">C:\xampp\htdocs\ssdream</code>
              </p>
            </div>
          </div>

          {/* Mode Switcher Tabs */}
          <div className="flex items-center gap-1 bg-slate-900 p-1 rounded-xl border border-slate-800 text-sm">
            <button
              onClick={() => setActiveTab('preview')}
              className={`px-3.5 py-1.5 rounded-lg font-medium transition flex items-center gap-1.5 ${
                activeTab === 'preview'
                  ? 'bg-sky-600 text-white shadow'
                  : 'text-slate-400 hover:text-slate-200'
              }`}
            >
              <Eye className="w-4 h-4" /> Live Simulator
            </button>
            <button
              onClick={() => setActiveTab('files')}
              className={`px-3.5 py-1.5 rounded-lg font-medium transition flex items-center gap-1.5 ${
                activeTab === 'files'
                  ? 'bg-sky-600 text-white shadow'
                  : 'text-slate-400 hover:text-slate-200'
              }`}
            >
              <FileCode className="w-4 h-4" /> Code Explorer
            </button>
            <button
              onClick={() => setActiveTab('database')}
              className={`px-3.5 py-1.5 rounded-lg font-medium transition flex items-center gap-1.5 ${
                activeTab === 'database'
                  ? 'bg-sky-600 text-white shadow'
                  : 'text-slate-400 hover:text-slate-200'
              }`}
            >
              <Database className="w-4 h-4" /> Database Schema
            </button>
            <button
              onClick={() => setActiveTab('setup')}
              className={`px-3.5 py-1.5 rounded-lg font-medium transition flex items-center gap-1.5 ${
                activeTab === 'setup'
                  ? 'bg-sky-600 text-white shadow'
                  : 'text-slate-400 hover:text-slate-200'
              }`}
            >
              <Server className="w-4 h-4" /> Testing Guide
            </button>
          </div>
        </div>
      </header>

      {/* Copy Notification Toast */}
      {copiedNotification && (
        <div className="fixed bottom-5 right-5 z-50 bg-emerald-600 text-white text-xs px-4 py-2.5 rounded-xl shadow-xl flex items-center gap-2">
          <CheckCircle className="w-4 h-4" /> Copied {copiedNotification} to clipboard
        </div>
      )}

      {/* Main Container */}
      <main className="flex-1 max-w-7xl w-full mx-auto p-4 md:p-6 space-y-6">
        {/* ====================================================
            TAB 1: LIVE SIMULATOR
        ==================================================== */}
        {activeTab === 'preview' && (
          <div className="space-y-4">
            {/* View Switcher Sub-bar */}
            <div className="bg-slate-950 p-3 rounded-2xl border border-slate-800 flex flex-wrap items-center justify-between gap-3">
              <div className="flex items-center gap-1 overflow-x-auto text-xs py-1">
                {/* Part 7 Controls */}
                <span className="text-amber-400 font-bold px-2 uppercase tracking-wider text-[10px] flex items-center gap-1">
                  <ShieldCheck className="w-3.5 h-3.5" /> Part 7 Admin &amp; Member Suite:
                </span>
                <button
                  onClick={() => setPreviewView('admin_members')}
                  className={`px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5 whitespace-nowrap ${
                    previewView === 'admin_members'
                      ? 'bg-amber-600 text-white shadow-lg shadow-amber-500/20'
                      : 'bg-slate-900 text-slate-300 hover:bg-slate-800'
                  }`}
                >
                  <Users className="w-3.5 h-3.5" /> Members (admin/members.php)
                </button>
                <button
                  onClick={() => setPreviewView('admin_kyc')}
                  className={`px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5 whitespace-nowrap ${
                    previewView === 'admin_kyc'
                      ? 'bg-amber-600 text-white shadow-lg shadow-amber-500/20'
                      : 'bg-slate-900 text-slate-300 hover:bg-slate-800'
                  }`}
                >
                  <CheckSquare className="w-3.5 h-3.5" /> KYC Verifications (admin/kyc.php)
                  {simKycRequests.filter(k => k.status === 'pending').length > 0 && (
                    <span className="bg-amber-400 text-slate-950 text-[10px] px-1.5 py-0.2 rounded-full font-bold">
                      {simKycRequests.filter(k => k.status === 'pending').length}
                    </span>
                  )}
                </button>
                <button
                  onClick={() => setPreviewView('admin_settings')}
                  className={`px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5 whitespace-nowrap ${
                    previewView === 'admin_settings'
                      ? 'bg-amber-600 text-white shadow-lg shadow-amber-500/20'
                      : 'bg-slate-900 text-slate-300 hover:bg-slate-800'
                  }`}
                >
                  <Sliders className="w-3.5 h-3.5" /> Settings (admin/settings.php)
                </button>
                <button
                  onClick={() => setPreviewView('user_support')}
                  className={`px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5 whitespace-nowrap ${
                    previewView === 'user_support'
                      ? 'bg-sky-600 text-white shadow'
                      : 'bg-slate-900 text-slate-300 hover:bg-slate-800'
                  }`}
                >
                  <Headphones className="w-3.5 h-3.5" /> Support Helpdesk (user/support.php)
                </button>
                <button
                  onClick={() => setPreviewView('admin_support')}
                  className={`px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5 whitespace-nowrap ${
                    previewView === 'admin_support'
                      ? 'bg-purple-600 text-white shadow'
                      : 'bg-slate-900 text-slate-300 hover:bg-slate-800'
                  }`}
                >
                  <MessageSquare className="w-3.5 h-3.5" /> Admin Helpdesk (admin/support.php)
                </button>
                <button
                  onClick={() => setPreviewView('user_notifications')}
                  className={`px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5 whitespace-nowrap ${
                    previewView === 'user_notifications'
                      ? 'bg-indigo-600 text-white shadow'
                      : 'bg-slate-900 text-slate-300 hover:bg-slate-800'
                  }`}
                >
                  <Bell className="w-3.5 h-3.5" /> Notifications (user/notifications.php)
                  {simNotifications.filter(n => !n.isRead).length > 0 && (
                    <span className="bg-rose-500 text-white text-[10px] px-1.5 py-0.2 rounded-full font-bold">
                      {simNotifications.filter(n => !n.isRead).length}
                    </span>
                  )}
                </button>

                <span className="text-slate-600 font-bold px-2 uppercase tracking-wider text-[10px]">
                  Part 6 Wallet &amp; Previous:
                </span>
                <button
                  onClick={() => setPreviewView('wallet')}
                  className={`px-2.5 py-1.5 rounded-lg font-medium transition flex items-center gap-1 ${
                    previewView === 'wallet' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-slate-200'
                  }`}
                >
                  <Wallet className="w-3 h-3" /> Wallet
                </button>
                <button
                  onClick={() => setPreviewView('withdraw')}
                  className={`px-2.5 py-1.5 rounded-lg font-medium transition flex items-center gap-1 ${
                    previewView === 'withdraw' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-slate-200'
                  }`}
                >
                  <DollarSign className="w-3 h-3" /> Payout
                </button>
                <button
                  onClick={() => setPreviewView('admin_withdrawals')}
                  className={`px-2.5 py-1.5 rounded-lg font-medium transition flex items-center gap-1 ${
                    previewView === 'admin_withdrawals' ? 'bg-amber-600 text-white' : 'text-slate-400 hover:text-slate-200'
                  }`}
                >
                  Admin Payouts
                </button>

                <span className="text-slate-600 font-bold px-2 uppercase tracking-wider text-[10px]">
                  Other Modules:
                </span>
                <button
                  onClick={() => setPreviewView('income_user')}
                  className={`px-2.5 py-1.5 rounded-lg font-medium transition flex items-center gap-1 ${
                    previewView === 'income_user' ? 'bg-sky-600 text-white' : 'text-slate-400 hover:text-slate-200'
                  }`}
                >
                  <TrendingUp className="w-3 h-3" /> Incomes
                </button>
                <button
                  onClick={() => setPreviewView('binary_tree')}
                  className={`px-2.5 py-1.5 rounded-lg font-medium transition flex items-center gap-1 ${
                    previewView === 'binary_tree' ? 'bg-sky-600 text-white' : 'text-slate-400 hover:text-slate-200'
                  }`}
                >
                  <GitBranch className="w-3 h-3" /> Binary Tree
                </button>
                <button
                  onClick={() => setPreviewView('packages')}
                  className={`px-2.5 py-1.5 rounded-lg font-medium transition ${
                    previewView === 'packages' ? 'bg-sky-600 text-white' : 'text-slate-400 hover:text-slate-200'
                  }`}
                >
                  Packages
                </button>
                <button
                  onClick={() => setPreviewView('upgrade_history')}
                  className={`px-2.5 py-1.5 rounded-lg font-medium transition flex items-center gap-1 ${
                    previewView === 'upgrade_history' ? 'bg-sky-600 text-white' : 'text-slate-400 hover:text-slate-200'
                  }`}
                >
                  <History className="w-3 h-3" /> Upgrades
                </button>
                <button
                  onClick={() => setPreviewView('user_dash')}
                  className={`px-2.5 py-1.5 rounded-lg font-medium transition ${
                    previewView === 'user_dash' ? 'bg-sky-600 text-white' : 'text-slate-400 hover:text-slate-200'
                  }`}
                >
                  Dashboard
                </button>
              </div>

              <div className="flex items-center gap-2 text-xs">
                <span className="text-slate-400 font-mono">2&times;2 Structure:</span>
                <span className="bg-emerald-500/20 text-emerald-400 font-bold px-2.5 py-0.5 rounded-full border border-emerald-500/30">
                  Max 2 Children
                </span>
              </div>
            </div>

            {/* Unauthorized Alert if triggered */}
            {unauthorizedAlert && (
              <div className="bg-rose-500/20 border border-rose-500/40 text-rose-300 p-3 rounded-xl text-xs flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <ShieldAlert className="w-4 h-4 text-rose-400" />
                  <span>{unauthorizedAlert}</span>
                </div>
                <button
                  onClick={() => setTreeRootId(1)}
                  className="bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold px-2.5 py-1 rounded"
                >
                  Reset to My Root
                </button>
              </div>
            )}

            {/* Simulated Browser Frame */}
            <div className="bg-slate-950 rounded-2xl border border-slate-800 overflow-hidden shadow-2xl">
              {/* Browser Address Bar */}
              <div className="bg-slate-900/90 px-4 py-2 border-b border-slate-800 flex items-center justify-between text-xs">
                <div className="flex items-center gap-2">
                  <span className="w-3 h-3 rounded-full bg-rose-500/80 inline-block"></span>
                  <span className="w-3 h-3 rounded-full bg-amber-500/80 inline-block"></span>
                  <span className="w-3 h-3 rounded-full bg-emerald-500/80 inline-block"></span>
                  <span className="text-slate-400 font-mono ml-2">
                    http://localhost/ssdream/{
                      previewView === 'admin_members'
                        ? 'admin/members.php'
                        : previewView === 'admin_kyc'
                        ? 'admin/kyc.php'
                        : previewView === 'admin_settings'
                        ? 'admin/settings.php'
                        : previewView === 'admin_support'
                        ? 'admin/support.php'
                        : previewView === 'user_support'
                        ? 'user/support.php'
                        : previewView === 'user_notifications'
                        ? 'user/notifications.php'
                        : previewView === 'wallet'
                        ? 'user/wallet.php'
                        : previewView === 'withdraw'
                        ? 'user/withdraw.php'
                        : previewView === 'transactions'
                        ? 'user/transactions.php'
                        : previewView === 'admin_withdrawals'
                        ? 'admin/withdrawals.php'
                        : previewView === 'income_user'
                        ? 'user/income.php'
                        : previewView === 'income_admin'
                        ? 'admin/incomes.php'
                        : previewView === 'binary_tree'
                        ? `user/binary-tree.php${treeRootId !== 1 ? `?id=${treeRootId}` : ''}`
                        : previewView === 'team'
                        ? 'user/team.php'
                        : previewView === 'sponsor'
                        ? 'user/sponsor.php'
                        : previewView === 'admin_tree'
                        ? `admin/tree.php${adminInspectedId !== 1 ? `?id=${adminInspectedId}` : ''}`
                        : previewView === 'user_dash'
                        ? 'user/dashboard.php'
                        : `${previewView}.php`
                    }
                  </span>
                </div>
                <div className="text-[11px] text-emerald-400 flex items-center gap-1 font-mono">
                  <CheckCircle className="w-3.5 h-3.5" /> PHP 8.2 &bull; PDO Ledger &bull; Row Locking
                </div>
              </div>

              {/* Browser Body */}
              <div className="p-0 overflow-hidden min-h-[520px]">
                {/* ====================================================
                    PART 7: ADMIN MEMBER MANAGEMENT (admin/members.php)
                ==================================================== */}
                {previewView === 'admin_members' && (
                  <div className="bg-slate-900 p-6 text-slate-100 text-xs">
                    <div className="max-w-6xl mx-auto space-y-4">
                      {/* Top Banner */}
                      <div className="bg-gradient-to-r from-amber-950 via-slate-900 to-slate-950 p-5 rounded-2xl border border-amber-500/30 flex flex-wrap justify-between items-center gap-3 shadow-sm">
                        <div>
                          <div className="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span className="bg-amber-500/20 text-amber-300 border border-amber-400/30 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                              Part 7 Admin Console
                            </span>
                            <span className="bg-emerald-500/20 text-emerald-300 text-[10px] font-bold px-2 py-0.5 rounded-full">
                              Active Members: {simMembers.filter(m => m.status === 'active').length}
                            </span>
                          </div>
                          <h3 className="font-bold text-base text-white flex items-center gap-2">
                            <Users className="w-5 h-5 text-amber-400" /> Member Directory &amp; Account Control (admin/members.php)
                          </h3>
                          <p className="text-slate-300 text-[11px]">
                            Comprehensive member administration. Manage account statuses (Active / Blocked), reset passwords, and inspect team placement.
                          </p>
                        </div>
                        <span className="bg-slate-800 text-slate-300 px-3 py-1.5 rounded-xl border border-slate-700 font-mono text-[11px]">
                          Total Records: <strong className="text-white">{simMembers.length}</strong>
                        </span>
                      </div>

                      {memberActionAlert && (
                        <div className="bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 p-3 rounded-xl flex items-center justify-between text-xs">
                          <span className="flex items-center gap-2">
                            <CheckCircle className="w-4 h-4 text-emerald-400" /> {memberActionAlert}
                          </span>
                          <button onClick={() => setMemberActionAlert(null)} className="text-emerald-400 font-bold hover:text-white">&times;</button>
                        </div>
                      )}

                      {/* KPI Cards */}
                      <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div className="bg-slate-950 p-3.5 rounded-xl border border-slate-800">
                          <span className="text-slate-400 text-[10px] uppercase font-bold block">Total Registered</span>
                          <span className="text-xl font-bold text-white">{simMembers.length}</span>
                          <span className="text-[10px] text-slate-500 block mt-0.5">Database users</span>
                        </div>
                        <div className="bg-slate-950 p-3.5 rounded-xl border border-slate-800">
                          <span className="text-slate-400 text-[10px] uppercase font-bold block">Active Accounts</span>
                          <span className="text-xl font-bold text-emerald-400">{simMembers.filter(m => m.status === 'active').length}</span>
                          <span className="text-[10px] text-emerald-500/80 block mt-0.5">Normal access</span>
                        </div>
                        <div className="bg-slate-950 p-3.5 rounded-xl border border-slate-800">
                          <span className="text-slate-400 text-[10px] uppercase font-bold block">Blocked Accounts</span>
                          <span className="text-xl font-bold text-rose-400">{simMembers.filter(m => m.status === 'blocked').length}</span>
                          <span className="text-[10px] text-rose-500/80 block mt-0.5">Suspended</span>
                        </div>
                        <div className="bg-slate-950 p-3.5 rounded-xl border border-slate-800">
                          <span className="text-slate-400 text-[10px] uppercase font-bold block">System Earned Total</span>
                          <span className="text-xl font-bold text-amber-400 font-mono">
                            ₹{simMembers.reduce((acc, m) => acc + m.totalIncome, 0).toLocaleString('en-IN')}
                          </span>
                          <span className="text-[10px] text-slate-500 block mt-0.5">Distributed bonuses</span>
                        </div>
                      </div>

                      {/* Search & Filter Bar */}
                      <div className="bg-slate-950 p-3 rounded-xl border border-slate-800 flex flex-wrap gap-2 items-center justify-between">
                        <div className="flex items-center gap-2 flex-1 min-w-[200px]">
                          <Search className="w-4 h-4 text-slate-400" />
                          <input
                            type="text"
                            placeholder="Search username, name, mobile, email..."
                            value={memberSearchQuery}
                            onChange={(e) => setMemberSearchQuery(e.target.value)}
                            className="bg-slate-900 border border-slate-700 text-white rounded-lg px-3 py-1.5 text-xs w-full focus:outline-none focus:border-amber-400"
                          />
                        </div>

                        <div className="flex items-center gap-2">
                          <select
                            value={memberStatusFilter}
                            onChange={(e) => setMemberStatusFilter(e.target.value as any)}
                            className="bg-slate-900 border border-slate-700 text-slate-300 rounded-lg px-2.5 py-1.5 text-xs"
                          >
                            <option value="all">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="blocked">Blocked</option>
                          </select>

                          <select
                            value={memberLevelFilter}
                            onChange={(e) => setMemberLevelFilter(e.target.value)}
                            className="bg-slate-900 border border-slate-700 text-slate-300 rounded-lg px-2.5 py-1.5 text-xs"
                          >
                            <option value="">All Levels</option>
                            {[1, 2, 3, 4, 5, 6, 7].map((lvl) => (
                              <option key={lvl} value={lvl}>Level {lvl}</option>
                            ))}
                          </select>
                        </div>
                      </div>

                      {/* Members Table */}
                      <div className="bg-slate-950 rounded-xl border border-slate-800 overflow-hidden shadow-xs">
                        <div className="overflow-x-auto">
                          <table className="w-full text-left border-collapse text-xs">
                            <thead>
                              <tr className="bg-slate-900 text-slate-400 text-[11px] border-b border-slate-800">
                                <th className="p-3 pl-4">Member Info</th>
                                <th className="p-3">Contact</th>
                                <th className="p-3">Sponsor / Leg</th>
                                <th className="p-3">Level</th>
                                <th className="p-3">Wallet Balance</th>
                                <th className="p-3">KYC</th>
                                <th className="p-3">Status</th>
                                <th className="p-3 pr-4 text-right">Actions</th>
                              </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60 font-sans">
                              {simMembers
                                .filter((m) => {
                                  if (memberSearchQuery) {
                                    const q = memberSearchQuery.toLowerCase();
                                    const match = m.username.toLowerCase().includes(q) || m.fullName.toLowerCase().includes(q) || m.mobile.includes(q);
                                    if (!match) return false;
                                  }
                                  if (memberStatusFilter !== 'all' && m.status !== memberStatusFilter) return false;
                                  if (memberLevelFilter && m.level.toString() !== memberLevelFilter) return false;
                                  return true;
                                })
                                .map((m) => (
                                  <tr key={m.id} className="hover:bg-slate-900/60 transition">
                                    <td className="p-3 pl-4">
                                      <div className="font-bold text-white">{m.fullName}</div>
                                      <div className="text-amber-400 font-mono text-[11px]">@{m.username}</div>
                                      <span className="text-[10px] text-slate-500 font-mono">ID: #{m.id}</span>
                                    </td>
                                    <td className="p-3">
                                      <div className="text-slate-300">{m.mobile}</div>
                                      <div className="text-slate-400 text-[11px]">{m.email}</div>
                                      <div className="text-[10px] text-slate-500">Joined: {m.joinDate}</div>
                                    </td>
                                    <td className="p-3">
                                      <div className="text-slate-300 font-mono">@{m.sponsor}</div>
                                      {m.leg && (
                                        <span className={`text-[10px] px-1.5 py-0.2 rounded font-bold uppercase ${
                                          m.leg === 'left' ? 'bg-blue-500/20 text-blue-300' : 'bg-purple-500/20 text-purple-300'
                                        }`}>
                                          {m.leg} Leg
                                        </span>
                                      )}
                                    </td>
                                    <td className="p-3">
                                      <span className="bg-amber-400/20 text-amber-300 font-bold px-2 py-0.5 rounded border border-amber-400/30">
                                        Level {m.level}
                                      </span>
                                    </td>
                                    <td className="p-3 font-mono">
                                      <div className="font-bold text-emerald-400">₹{m.walletBalance.toLocaleString('en-IN')}</div>
                                      <div className="text-[10px] text-slate-400">Total: ₹{m.totalIncome.toLocaleString('en-IN')}</div>
                                    </td>
                                    <td className="p-3">
                                      <span className={`px-2 py-0.5 rounded-full text-[10px] font-bold capitalize ${
                                        m.kyc === 'approved' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' :
                                        m.kyc === 'rejected' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' :
                                        'bg-amber-500/20 text-amber-400 border border-amber-500/30'
                                      }`}>
                                        {m.kyc}
                                      </span>
                                    </td>
                                    <td className="p-3">
                                      {m.status === 'active' ? (
                                        <span className="bg-emerald-500/20 text-emerald-400 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 w-max">
                                          <CheckCircle className="w-3 h-3" /> Active
                                        </span>
                                      ) : (
                                        <span className="bg-rose-500/20 text-rose-400 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 w-max">
                                          <UserX className="w-3 h-3" /> Blocked
                                        </span>
                                      )}
                                    </td>
                                    <td className="p-3 pr-4 text-right">
                                      <div className="flex items-center justify-end gap-1.5">
                                        <button
                                          onClick={() => {
                                            const updated = simMembers.map(item => {
                                              if (item.id === m.id) {
                                                const next = item.status === 'active' ? 'blocked' : 'active';
                                                return { ...item, status: next as 'active' | 'blocked' };
                                              }
                                              return item;
                                            });
                                            setSimMembers(updated);
                                            setMemberActionAlert(`Member @${m.username} account status toggled to ${m.status === 'active' ? 'BLOCKED' : 'ACTIVE'}.`);
                                            setTimeout(() => setMemberActionAlert(null), 3000);
                                          }}
                                          className={`px-2.5 py-1 rounded text-[11px] font-semibold transition ${
                                            m.status === 'active'
                                              ? 'bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/30'
                                              : 'bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/30'
                                          }`}
                                        >
                                          {m.status === 'active' ? 'Block' : 'Activate'}
                                        </button>
                                        <button
                                          onClick={() => {
                                            setMemberActionAlert(`Password for @${m.username} reset to default: SSdream@2026`);
                                            setTimeout(() => setMemberActionAlert(null), 3500);
                                          }}
                                          className="px-2.5 py-1 rounded text-[11px] font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700"
                                        >
                                          Reset Pass
                                        </button>
                                      </div>
                                    </td>
                                  </tr>
                                ))}
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    PART 7: ADMIN KYC VERIFICATION (admin/kyc.php)
                ==================================================== */}
                {previewView === 'admin_kyc' && (
                  <div className="bg-slate-900 p-6 text-slate-100 text-xs">
                    <div className="max-w-6xl mx-auto space-y-4">
                      {/* Top Banner */}
                      <div className="bg-gradient-to-r from-amber-950 via-slate-900 to-slate-950 p-5 rounded-2xl border border-amber-500/30 flex flex-wrap justify-between items-center gap-3 shadow-sm">
                        <div>
                          <div className="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span className="bg-amber-500/20 text-amber-300 border border-amber-400/30 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                              Compliance Center
                            </span>
                            <span className="bg-amber-400 text-slate-950 text-[10px] font-bold px-2.5 py-0.5 rounded-full">
                              Pending Verifications: {simKycRequests.filter(k => k.status === 'pending').length}
                            </span>
                          </div>
                          <h3 className="font-bold text-base text-white flex items-center gap-2">
                            <CheckSquare className="w-5 h-5 text-amber-400" /> Member KYC Verification (admin/kyc.php)
                          </h3>
                          <p className="text-slate-300 text-[11px]">
                            Review government identification documents and bank details submitted by members to authorize payout withdrawals.
                          </p>
                        </div>
                      </div>

                      {kycActionAlert && (
                        <div className="bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 p-3 rounded-xl flex items-center justify-between text-xs">
                          <span className="flex items-center gap-2">
                            <CheckCircle className="w-4 h-4 text-emerald-400" /> {kycActionAlert}
                          </span>
                          <button onClick={() => setKycActionAlert(null)} className="text-emerald-400 font-bold hover:text-white">&times;</button>
                        </div>
                      )}

                      {/* Filter Tabs */}
                      <div className="flex items-center gap-2 bg-slate-950 p-2 rounded-xl border border-slate-800">
                        {(['pending', 'approved', 'rejected', 'all'] as const).map((tabKey) => (
                          <button
                            key={tabKey}
                            onClick={() => setKycTab(tabKey)}
                            className={`px-3 py-1.5 rounded-lg text-xs font-semibold capitalize transition ${
                              kycTab === tabKey
                                ? 'bg-amber-500 text-slate-950 font-bold shadow'
                                : 'text-slate-400 hover:text-white'
                            }`}
                          >
                            {tabKey} ({
                              tabKey === 'all'
                                ? simKycRequests.length
                                : simKycRequests.filter(k => k.status === tabKey).length
                            })
                          </button>
                        ))}
                      </div>

                      {/* Submissions Table */}
                      <div className="bg-slate-950 rounded-xl border border-slate-800 overflow-hidden shadow-xs">
                        <div className="overflow-x-auto">
                          <table className="w-full text-left border-collapse text-xs">
                            <thead>
                              <tr className="bg-slate-900 text-slate-400 text-[11px] border-b border-slate-800">
                                <th className="p-3 pl-4">Member</th>
                                <th className="p-3">Document Info</th>
                                <th className="p-3">Bank &amp; UPI Account</th>
                                <th className="p-3">Status</th>
                                <th className="p-3">Date</th>
                                <th className="p-3 pr-4 text-right">Verification Action</th>
                              </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60 font-sans">
                              {simKycRequests
                                .filter(k => kycTab === 'all' || k.status === kycTab)
                                .map((k) => (
                                  <tr key={k.id} className="hover:bg-slate-900/60 transition">
                                    <td className="p-3 pl-4">
                                      <div className="font-bold text-white">{k.fullName}</div>
                                      <div className="text-amber-400 font-mono text-[11px]">@{k.username}</div>
                                      <div className="text-slate-400 text-[10px]">{k.mobile}</div>
                                    </td>
                                    <td className="p-3">
                                      <div className="font-semibold text-white">{k.docType}</div>
                                      <div className="text-amber-300 font-mono text-[11px]">{k.docNumber}</div>
                                      <span className="text-[10px] text-sky-400 underline cursor-pointer">Preview Uploaded Slip</span>
                                    </td>
                                    <td className="p-3 font-mono">
                                      <div className="text-white font-bold">{k.bankName}</div>
                                      <div className="text-slate-400 text-[10px]">A/C: {k.accNo} (IFSC: {k.ifsc})</div>
                                      <div className="text-sky-300 text-[10px]">UPI: {k.upi}</div>
                                    </td>
                                    <td className="p-3">
                                      <span className={`px-2 py-0.5 rounded-full text-[10px] font-bold capitalize ${
                                        k.status === 'approved' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' :
                                        k.status === 'rejected' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' :
                                        'bg-amber-500/20 text-amber-400 border border-amber-500/30'
                                      }`}>
                                        {k.status}
                                      </span>
                                      {k.adminNotes && (
                                        <div className="text-[10px] text-rose-400 mt-1 max-w-xs">{k.adminNotes}</div>
                                      )}
                                    </td>
                                    <td className="p-3 text-slate-400 text-[11px]">{k.date}</td>
                                    <td className="p-3 pr-4 text-right">
                                      {k.status === 'pending' ? (
                                        <div className="flex items-center justify-end gap-1.5">
                                          <button
                                            onClick={() => {
                                              const updated = simKycRequests.map(item => item.id === k.id ? { ...item, status: 'approved' as const, adminNotes: 'Verified by Admin' } : item);
                                              setSimKycRequests(updated);
                                              setKycActionAlert(`KYC document for @${k.username} successfully APPROVED!`);
                                              setTimeout(() => setKycActionAlert(null), 3000);
                                            }}
                                            className="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-2.5 py-1 rounded text-[11px] transition"
                                          >
                                            Approve
                                          </button>
                                          <button
                                            onClick={() => {
                                              const updated = simKycRequests.map(item => item.id === k.id ? { ...item, status: 'rejected' as const, adminNotes: 'Document blurry / mismatch' } : item);
                                              setSimKycRequests(updated);
                                              setKycActionAlert(`KYC document for @${k.username} marked as REJECTED.`);
                                              setTimeout(() => setKycActionAlert(null), 3000);
                                            }}
                                            className="bg-rose-600 hover:bg-rose-500 text-white font-bold px-2.5 py-1 rounded text-[11px] transition"
                                          >
                                            Reject
                                          </button>
                                        </div>
                                      ) : (
                                        <span className="text-slate-500 text-[11px]">Completed</span>
                                      )}
                                    </td>
                                  </tr>
                                ))}
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    PART 7: ADMIN SYSTEM SETTINGS (admin/settings.php)
                ==================================================== */}
                {previewView === 'admin_settings' && (
                  <div className="bg-slate-900 p-6 text-slate-100 text-xs">
                    <div className="max-w-5xl mx-auto space-y-4">
                      {/* Top Banner */}
                      <div className="bg-gradient-to-r from-amber-950 via-slate-900 to-slate-950 p-5 rounded-2xl border border-amber-500/30 flex flex-wrap justify-between items-center gap-3 shadow-sm">
                        <div>
                          <div className="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span className="bg-amber-500/20 text-amber-300 border border-amber-400/30 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                              System Configuration
                            </span>
                            <span className="bg-sky-500/20 text-sky-300 text-[10px] font-bold px-2.5 py-0.5 rounded-full">
                              Business Rules &amp; Bank Details
                            </span>
                          </div>
                          <h3 className="font-bold text-base text-white flex items-center gap-2">
                            <Sliders className="w-5 h-5 text-amber-400" /> Platform &amp; Payout Settings (admin/settings.php)
                          </h3>
                          <p className="text-slate-300 text-[11px]">
                            Live database parameters for minimum withdrawals, TDS deductions, company bank deposits, and dashboard announcements.
                          </p>
                        </div>
                      </div>

                      {settingsSavedAlert && (
                        <div className="bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 p-3 rounded-xl flex items-center justify-between text-xs">
                          <span className="flex items-center gap-2">
                            <CheckCircle className="w-4 h-4 text-emerald-400" /> {settingsSavedAlert}
                          </span>
                          <button onClick={() => setSettingsSavedAlert(null)} className="text-emerald-400 font-bold hover:text-white">&times;</button>
                        </div>
                      )}

                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {/* 1. General & Support */}
                        <div className="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-3">
                          <h4 className="font-bold text-amber-400 text-xs flex items-center gap-1.5 uppercase">
                            <FileText className="w-4 h-4" /> General &amp; Contact Info
                          </h4>
                          <div>
                            <label className="text-slate-400 text-[11px] block mb-1">Company / Platform Title</label>
                            <input
                              type="text"
                              value={simSettings.siteName}
                              onChange={(e) => setSimSettings({ ...simSettings, siteName: e.target.value })}
                              className="bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs w-full focus:outline-none focus:border-amber-400"
                            />
                          </div>
                          <div>
                            <label className="text-slate-400 text-[11px] block mb-1">Support Email</label>
                            <input
                              type="email"
                              value={simSettings.supportEmail}
                              onChange={(e) => setSimSettings({ ...simSettings, supportEmail: e.target.value })}
                              className="bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs w-full focus:outline-none focus:border-amber-400"
                            />
                          </div>
                          <div className="grid grid-cols-2 gap-2">
                            <div>
                              <label className="text-slate-400 text-[11px] block mb-1">Support Phone</label>
                              <input
                                type="text"
                                value={simSettings.supportPhone}
                                onChange={(e) => setSimSettings({ ...simSettings, supportPhone: e.target.value })}
                                className="bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs w-full focus:outline-none focus:border-amber-400"
                              />
                            </div>
                            <div>
                              <label className="text-slate-400 text-[11px] block mb-1">WhatsApp Number</label>
                              <input
                                type="text"
                                value={simSettings.whatsapp}
                                onChange={(e) => setSimSettings({ ...simSettings, whatsapp: e.target.value })}
                                className="bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs w-full focus:outline-none focus:border-amber-400"
                              />
                            </div>
                          </div>
                        </div>

                        {/* 2. Payout & Deduction Parameters */}
                        <div className="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-3">
                          <h4 className="font-bold text-amber-400 text-xs flex items-center gap-1.5 uppercase">
                            <DollarSign className="w-4 h-4" /> Withdrawal &amp; Payout Rules
                          </h4>
                          <div>
                            <label className="text-slate-400 text-[11px] block mb-1">Minimum Withdrawal Limit (₹)</label>
                            <input
                              type="number"
                              value={simSettings.minWithdrawal}
                              onChange={(e) => setSimSettings({ ...simSettings, minWithdrawal: Number(e.target.value) })}
                              className="bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs w-full font-mono focus:outline-none focus:border-amber-400"
                            />
                          </div>
                          <div className="grid grid-cols-2 gap-2">
                            <div>
                              <label className="text-slate-400 text-[11px] block mb-1">TDS Deduction (%)</label>
                              <input
                                type="number"
                                value={simSettings.tdsPercent}
                                onChange={(e) => setSimSettings({ ...simSettings, tdsPercent: Number(e.target.value) })}
                                className="bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs w-full font-mono focus:outline-none focus:border-amber-400"
                              />
                            </div>
                            <div>
                              <label className="text-slate-400 text-[11px] block mb-1">Admin Maintenance (%)</label>
                              <input
                                type="number"
                                value={simSettings.adminChargePercent}
                                onChange={(e) => setSimSettings({ ...simSettings, adminChargePercent: Number(e.target.value) })}
                                className="bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs w-full font-mono focus:outline-none focus:border-amber-400"
                              />
                            </div>
                          </div>
                          <div className="p-2.5 rounded bg-slate-900/80 border border-slate-800 text-[11px] text-slate-400">
                            Locked Income Formulas: Direct 100%, Level 50% (L2-L7), Sponsor 50% (L2-Unlimited).
                          </div>
                        </div>

                        {/* 3. Company Receiving Bank Details */}
                        <div className="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-3">
                          <h4 className="font-bold text-amber-400 text-xs flex items-center gap-1.5 uppercase">
                            <CreditCard className="w-4 h-4" /> Company Receiving Bank Account (For Upgrades)
                          </h4>
                          <div>
                            <label className="text-slate-400 text-[11px] block mb-1">Bank Name</label>
                            <input
                              type="text"
                              value={simSettings.companyBankName}
                              onChange={(e) => setSimSettings({ ...simSettings, companyBankName: e.target.value })}
                              className="bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs w-full focus:outline-none focus:border-amber-400"
                            />
                          </div>
                          <div className="grid grid-cols-2 gap-2">
                            <div>
                              <label className="text-slate-400 text-[11px] block mb-1">Account Number</label>
                              <input
                                type="text"
                                value={simSettings.companyAccountNumber}
                                onChange={(e) => setSimSettings({ ...simSettings, companyAccountNumber: e.target.value })}
                                className="bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs w-full font-mono focus:outline-none focus:border-amber-400"
                              />
                            </div>
                            <div>
                              <label className="text-slate-400 text-[11px] block mb-1">IFSC Code</label>
                              <input
                                type="text"
                                value={simSettings.companyIfscCode}
                                onChange={(e) => setSimSettings({ ...simSettings, companyIfscCode: e.target.value })}
                                className="bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs w-full font-mono focus:outline-none focus:border-amber-400"
                              />
                            </div>
                          </div>
                          <div>
                            <label className="text-slate-400 text-[11px] block mb-1">Company Official UPI ID</label>
                            <input
                              type="text"
                              value={simSettings.companyUpiId}
                              onChange={(e) => setSimSettings({ ...simSettings, companyUpiId: e.target.value })}
                              className="bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs w-full font-mono focus:outline-none focus:border-amber-400"
                            />
                          </div>
                        </div>

                        {/* 4. Dashboard Announcement Banner */}
                        <div className="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-3">
                          <h4 className="font-bold text-amber-400 text-xs flex items-center gap-1.5 uppercase">
                            <Bell className="w-4 h-4" /> Dashboard Announcement Banner
                          </h4>
                          <div>
                            <label className="text-slate-400 text-[11px] block mb-1">Notice Board Text</label>
                            <textarea
                              rows={3}
                              value={simSettings.noticeText}
                              onChange={(e) => setSimSettings({ ...simSettings, noticeText: e.target.value })}
                              className="bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs w-full focus:outline-none focus:border-amber-400"
                            />
                          </div>
                          <div>
                            <label className="text-slate-400 text-[11px] block mb-1">Banner Status</label>
                            <select
                              value={simSettings.noticeStatus}
                              onChange={(e) => setSimSettings({ ...simSettings, noticeStatus: e.target.value })}
                              className="bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs w-full"
                            >
                              <option value="active">Active (Visible to all members)</option>
                              <option value="inactive">Inactive (Hidden)</option>
                            </select>
                          </div>
                        </div>
                      </div>

                      <div className="text-right">
                        <button
                          onClick={() => {
                            setSettingsSavedAlert('Settings successfully saved and synced to database table `settings`!');
                            setTimeout(() => setSettingsSavedAlert(null), 3500);
                          }}
                          className="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-5 py-2.5 rounded-xl text-xs transition shadow"
                        >
                          Save All Settings
                        </button>
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    PART 7: USER SUPPORT TICKETS (user/support.php)
                ==================================================== */}
                {previewView === 'user_support' && (
                  <div className="bg-slate-50 p-6 text-slate-800 text-xs">
                    <div className="max-w-5xl mx-auto space-y-4">
                      {/* Top Banner */}
                      <div className="bg-gradient-to-r from-slate-900 to-indigo-950 text-white p-5 rounded-2xl flex flex-wrap justify-between items-center gap-3 shadow-sm">
                        <div>
                          <div className="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span className="bg-sky-500/20 text-sky-300 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                              Member Helpdesk
                            </span>
                          </div>
                          <h3 className="font-bold text-base text-white flex items-center gap-2">
                            <Headphones className="w-5 h-5 text-sky-400" /> Support Tickets &amp; Inquiries (user/support.php)
                          </h3>
                          <p className="text-slate-300 text-[11px]">
                            Connect directly with the administration team regarding withdrawals, package upgrades, or account assistance.
                          </p>
                        </div>
                      </div>

                      {ticketActionAlert && (
                        <div className="bg-emerald-500/20 border border-emerald-500/40 text-emerald-800 p-3 rounded-xl flex items-center justify-between text-xs">
                          <span className="flex items-center gap-2">
                            <CheckCircle className="w-4 h-4 text-emerald-600" /> {ticketActionAlert}
                          </span>
                          <button onClick={() => setTicketActionAlert(null)} className="text-emerald-800 font-bold">&times;</button>
                        </div>
                      )}

                      {/* Active Discussion Thread */}
                      {simTickets.find(t => t.id === selectedTicketThreadId) && (
                        <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                          <div className="bg-slate-900 text-white p-4 flex flex-wrap justify-between items-center gap-2">
                            <div>
                              <span className="bg-amber-400 text-slate-950 font-bold px-2 py-0.5 rounded text-[10px] mr-2">
                                {simTickets.find(t => t.id === selectedTicketThreadId)?.ticketNo}
                              </span>
                              <strong className="text-sm">{simTickets.find(t => t.id === selectedTicketThreadId)?.subject}</strong>
                              <span className="text-slate-400 text-[11px] ml-2">
                                ({simTickets.find(t => t.id === selectedTicketThreadId)?.category})
                              </span>
                            </div>
                            <span className="bg-sky-500/20 text-sky-300 text-[10px] font-bold px-2 py-0.5 rounded-full capitalize">
                              {simTickets.find(t => t.id === selectedTicketThreadId)?.status.replace('_', ' ')}
                            </span>
                          </div>

                          <div className="p-4 space-y-3 bg-slate-50 max-h-[300px] overflow-y-auto">
                            {simTickets.find(t => t.id === selectedTicketThreadId)?.messages.map((m) => (
                              <div key={m.id} className={`flex ${m.sender === 'user' ? 'justify-end' : 'justify-start'}`}>
                                <div className={`p-3 rounded-xl max-w-md ${
                                  m.sender === 'user' ? 'bg-sky-600 text-white' : 'bg-white text-slate-900 border border-slate-200'
                                }`}>
                                  <div className="flex justify-between items-center gap-4 text-[10px] mb-1 font-semibold">
                                    <span className={m.sender === 'user' ? 'text-sky-200' : 'text-amber-600'}>{m.name}</span>
                                    <span className="text-slate-400">{m.time}</span>
                                  </div>
                                  <p className="text-xs">{m.text}</p>
                                </div>
                              </div>
                            ))}
                          </div>

                          <div className="p-3 bg-white border-t border-slate-200 flex gap-2">
                            <input
                              type="text"
                              placeholder="Write a message to support..."
                              value={ticketReplyInput}
                              onChange={(e) => setTicketReplyInput(e.target.value)}
                              className="bg-slate-100 border border-slate-300 rounded-lg p-2 text-xs flex-1 focus:outline-none focus:border-sky-500"
                            />
                            <button
                              onClick={() => {
                                if (!ticketReplyInput.trim()) return;
                                const updated = simTickets.map(t => {
                                  if (t.id === selectedTicketThreadId) {
                                    return {
                                      ...t,
                                      messages: [
                                        ...t.messages,
                                        { id: Date.now(), sender: 'user' as const, name: 'Ramesh Sharma', text: ticketReplyInput, time: 'Just now' }
                                      ]
                                    };
                                  }
                                  return t;
                                });
                                setSimTickets(updated);
                                setTicketReplyInput('');
                                setTicketActionAlert('Reply sent to administration team.');
                                setTimeout(() => setTicketActionAlert(null), 3000);
                              }}
                              className="bg-sky-600 hover:bg-sky-500 text-white font-bold px-4 py-2 rounded-lg text-xs flex items-center gap-1.5 shadow"
                            >
                              <Send className="w-3.5 h-3.5" /> Send
                            </button>
                          </div>
                        </div>
                      )}

                      {/* Create New Ticket Form Card */}
                      <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                        <h4 className="font-bold text-slate-900 text-xs uppercase tracking-wider flex items-center gap-1.5">
                          <MessageSquare className="w-4 h-4 text-sky-600" /> Create New Support Ticket
                        </h4>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                          <div>
                            <label className="text-slate-500 text-[11px] block mb-1">Inquiry Category</label>
                            <select
                              value={newTicketCategory}
                              onChange={(e) => setNewTicketCategory(e.target.value)}
                              className="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 text-xs"
                            >
                              <option value="Withdrawal / Payout">Withdrawal / Payout</option>
                              <option value="Level Upgrade / Package">Level Upgrade / Package</option>
                              <option value="Direct / Sponsor Income">Direct / Sponsor Income</option>
                              <option value="KYC Verification">KYC Document Verification</option>
                              <option value="General Inquiry">General Inquiry</option>
                            </select>
                          </div>
                          <div className="md:col-span-2">
                            <label className="text-slate-500 text-[11px] block mb-1">Subject / Title</label>
                            <input
                              type="text"
                              placeholder="Brief summary of inquiry"
                              value={newTicketSubject}
                              onChange={(e) => setNewTicketSubject(e.target.value)}
                              className="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 text-xs"
                            />
                          </div>
                        </div>
                        <div>
                          <label className="text-slate-500 text-[11px] block mb-1">Describe Issue In Detail</label>
                          <textarea
                            rows={3}
                            placeholder="Provide full description, transaction ID, or account details..."
                            value={newTicketMessage}
                            onChange={(e) => setNewTicketMessage(e.target.value)}
                            className="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 text-xs"
                          />
                        </div>
                        <div className="text-right">
                          <button
                            onClick={() => {
                              if (!newTicketSubject.trim() || !newTicketMessage.trim()) {
                                alert('Please provide subject and message');
                                return;
                              }
                              const newTkt = {
                                id: Date.now(),
                                ticketNo: `TKT-${Date.now().toString().slice(-6)}`,
                                username: 'ramesh01',
                                fullName: 'Ramesh Sharma',
                                subject: newTicketSubject,
                                category: newTicketCategory,
                                priority: newTicketPriority as any,
                                status: 'open' as const,
                                updatedAt: 'Just now',
                                messages: [
                                  { id: 1, sender: 'user' as const, name: 'Ramesh Sharma', text: newTicketMessage, time: 'Just now' }
                                ]
                              };
                              setSimTickets([newTkt, ...simTickets]);
                              setSelectedTicketThreadId(newTkt.id);
                              setNewTicketSubject('');
                              setNewTicketMessage('');
                              setTicketActionAlert(`Ticket #${newTkt.ticketNo} created successfully!`);
                              setTimeout(() => setTicketActionAlert(null), 3000);
                            }}
                            className="bg-sky-600 hover:bg-sky-500 text-white font-bold px-4 py-2 rounded-xl text-xs shadow"
                          >
                            Submit Support Ticket
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    PART 7: ADMIN SUPPORT TICKETS (admin/support.php)
                ==================================================== */}
                {previewView === 'admin_support' && (
                  <div className="bg-slate-900 p-6 text-slate-100 text-xs">
                    <div className="max-w-6xl mx-auto space-y-4">
                      {/* Top Banner */}
                      <div className="bg-gradient-to-r from-purple-950 via-slate-900 to-slate-950 p-5 rounded-2xl border border-purple-500/30 flex flex-wrap justify-between items-center gap-3 shadow-sm">
                        <div>
                          <div className="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span className="bg-purple-500/20 text-purple-300 border border-purple-400/30 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                              Admin Support Desk
                            </span>
                            <span className="bg-amber-400 text-slate-950 text-[10px] font-bold px-2.5 py-0.5 rounded-full">
                              Open Inquiries: {simTickets.filter(t => t.status === 'open' || t.status === 'in_progress').length}
                            </span>
                          </div>
                          <h3 className="font-bold text-base text-white flex items-center gap-2">
                            <MessageSquare className="w-5 h-5 text-purple-400" /> Support Desk Management (admin/support.php)
                          </h3>
                          <p className="text-slate-300 text-[11px]">
                            Respond to member inquiries, escalate priority issues, and update resolution statuses in real time.
                          </p>
                        </div>
                      </div>

                      {/* Tickets Table */}
                      <div className="bg-slate-950 rounded-xl border border-slate-800 overflow-hidden shadow-xs">
                        <div className="overflow-x-auto">
                          <table className="w-full text-left border-collapse text-xs">
                            <thead>
                              <tr className="bg-slate-900 text-slate-400 text-[11px] border-b border-slate-800">
                                <th className="p-3 pl-4">Ticket No</th>
                                <th className="p-3">Member</th>
                                <th className="p-3">Category &amp; Subject</th>
                                <th className="p-3">Status</th>
                                <th className="p-3">Updated</th>
                                <th className="p-3 pr-4 text-right">Action</th>
                              </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60 font-sans">
                              {simTickets.map((t) => (
                                <tr key={t.id} className="hover:bg-slate-900/60 transition">
                                  <td className="p-3 pl-4 font-mono font-bold text-amber-400">{t.ticketNo}</td>
                                  <td className="p-3">
                                    <div className="font-bold text-white">{t.fullName}</div>
                                    <div className="text-slate-400 font-mono text-[11px]">@{t.username}</div>
                                  </td>
                                  <td className="p-3">
                                    <div className="text-white font-semibold">{t.subject}</div>
                                    <span className="text-[10px] text-slate-400">{t.category}</span>
                                  </td>
                                  <td className="p-3">
                                    <span className={`px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ${
                                      t.status === 'resolved' ? 'bg-emerald-500/20 text-emerald-400' :
                                      t.status === 'in_progress' ? 'bg-sky-500/20 text-sky-400' :
                                      'bg-amber-500/20 text-amber-400'
                                    }`}>
                                      {t.status.replace('_', ' ')}
                                    </span>
                                  </td>
                                  <td className="p-3 text-slate-400 text-[11px]">{t.updatedAt}</td>
                                  <td className="p-3 pr-4 text-right">
                                    <button
                                      onClick={() => {
                                        setSelectedTicketThreadId(t.id);
                                        setPreviewView('user_support');
                                      }}
                                      className="bg-purple-600 hover:bg-purple-500 text-white font-bold px-3 py-1 rounded text-[11px] transition"
                                    >
                                      Open Thread
                                    </button>
                                  </td>
                                </tr>
                              ))}
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    PART 7: USER NOTIFICATIONS (user/notifications.php)
                ==================================================== */}
                {previewView === 'user_notifications' && (
                  <div className="bg-slate-50 p-6 text-slate-800 text-xs">
                    <div className="max-w-4xl mx-auto space-y-4">
                      {/* Top Banner */}
                      <div className="bg-gradient-to-r from-slate-900 to-indigo-950 text-white p-5 rounded-2xl flex flex-wrap justify-between items-center gap-3 shadow-sm">
                        <div>
                          <div className="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span className="bg-indigo-500/20 text-indigo-300 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                              Member Alerts
                            </span>
                            <span className="bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                              Unread: {simNotifications.filter(n => !n.isRead).length}
                            </span>
                          </div>
                          <h3 className="font-bold text-base text-white flex items-center gap-2">
                            <Bell className="w-5 h-5 text-indigo-400" /> Notifications &amp; System Alerts (user/notifications.php)
                          </h3>
                          <p className="text-slate-300 text-[11px]">
                            Live notification inbox for payout requests, bonus additions, package upgrades, and KYC approvals.
                          </p>
                        </div>
                        <button
                          onClick={() => {
                            setSimNotifications(simNotifications.map(n => ({ ...n, isRead: true })));
                          }}
                          className="bg-white/10 hover:bg-white/20 text-white font-semibold px-3 py-1.5 rounded-lg text-xs transition"
                        >
                          Mark All As Read
                        </button>
                      </div>

                      {/* Notifications List */}
                      <div className="bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100 shadow-sm overflow-hidden">
                        {simNotifications.map((n) => (
                          <div key={n.id} className={`p-4 flex justify-between items-start gap-3 ${!n.isRead ? 'bg-indigo-50/40 border-l-4 border-l-indigo-600' : ''}`}>
                            <div className="space-y-1">
                              <div className="flex items-center gap-2">
                                {!n.isRead && (
                                  <span className="bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.2 rounded">NEW</span>
                                )}
                                <h4 className="font-bold text-slate-900 text-xs">{n.title}</h4>
                              </div>
                              <p className="text-slate-600 text-[11px]">{n.message}</p>
                            </div>
                            <span className="text-[10px] text-slate-400 whitespace-nowrap">{n.time}</span>
                          </div>
                        ))}
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    PART 6: USER WALLET (user/wallet.php)
                ==================================================== */}
                {previewView === 'wallet' && (
                  <div className="bg-slate-50 p-6 text-slate-800">
                    <div className="max-w-5xl mx-auto space-y-6">
                      {/* Top Welcome & KYC Status Banner */}
                      <div className="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white p-5 rounded-2xl shadow-sm flex flex-wrap justify-between items-center gap-4">
                        <div>
                          <div className="flex items-center gap-2 mb-1">
                            <span className="bg-emerald-500/20 text-emerald-300 text-xs px-2.5 py-0.5 rounded-full font-bold border border-emerald-500/30">
                              Part 6 Financial Wallet
                            </span>
                            <span className="bg-sky-500/20 text-sky-300 text-xs px-2.5 py-0.5 rounded-full font-bold">
                              Member: rohit99 (Level 2)
                            </span>
                          </div>
                          <h2 className="text-xl font-bold flex items-center gap-2">
                            <Wallet className="w-5 h-5 text-emerald-400" /> My Financial Wallet &amp; Balances
                          </h2>
                          <p className="text-xs text-slate-300 mt-1">
                            Live wallet ledger with persistent balance locking. Balance is updated exclusively by verified server-side transaction routines.
                          </p>
                        </div>

                        <div className="flex items-center gap-2">
                          <button
                            onClick={() => setPreviewView('withdraw')}
                            className="bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold px-4 py-2 rounded-xl text-xs flex items-center gap-1.5 transition shadow"
                          >
                            <DollarSign className="w-4 h-4" /> Request Payout &rarr;
                          </button>
                        </div>
                      </div>

                      {/* 5 Professional Cards as explicitly specified in Part 6 */}
                      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                        {/* 1. Current Balance */}
                        <div className="bg-white p-4 rounded-xl border-t-4 border-t-blue-600 border border-slate-200 shadow-xs">
                          <div className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">
                            Current Balance
                          </div>
                          <div className="text-2xl font-bold font-mono text-slate-900">
                            ₹{simWallet.balance.toLocaleString('en-IN')}
                          </div>
                          <div className="text-[10px] text-slate-500 mt-1">Net spendable ledger</div>
                        </div>

                        {/* 2. Total Credit */}
                        <div className="bg-white p-4 rounded-xl border-t-4 border-t-emerald-600 border border-slate-200 shadow-xs">
                          <div className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">
                            Total Credit
                          </div>
                          <div className="text-2xl font-bold font-mono text-emerald-600">
                            ₹{simWallet.totalCredit.toLocaleString('en-IN')}
                          </div>
                          <div className="text-[10px] text-emerald-600 flex items-center gap-0.5 mt-1">
                            <Check className="w-3 h-3" /> All incomes &amp; refunds
                          </div>
                        </div>

                        {/* 3. Total Debit */}
                        <div className="bg-white p-4 rounded-xl border-t-4 border-t-rose-600 border border-slate-200 shadow-xs">
                          <div className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">
                            Total Debit
                          </div>
                          <div className="text-2xl font-bold font-mono text-rose-600">
                            ₹{simWallet.totalDebit.toLocaleString('en-IN')}
                          </div>
                          <div className="text-[10px] text-rose-500 mt-1">Payouts &amp; withdrawals</div>
                        </div>

                        {/* 4. Available for Withdrawal */}
                        <div className="bg-white p-4 rounded-xl border-t-4 border-t-sky-500 border border-slate-200 shadow-xs">
                          <div className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">
                            Available for Payout
                          </div>
                          <div className="text-2xl font-bold font-mono text-sky-600">
                            ₹{simWallet.availableForWithdrawal.toLocaleString('en-IN')}
                          </div>
                          <div className="text-[10px] text-slate-500 mt-1">Instant withdraw capacity</div>
                        </div>

                        {/* 5. Pending Withdrawal */}
                        <div className="bg-white p-4 rounded-xl border-t-4 border-t-amber-500 border border-slate-200 shadow-xs">
                          <div className="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">
                            Pending Payout
                          </div>
                          <div className="text-2xl font-bold font-mono text-amber-500">
                            ₹{simWallet.pendingWithdrawal.toLocaleString('en-IN')}
                          </div>
                          <div className="text-[10px] text-amber-600 mt-1">Awaiting admin review</div>
                        </div>
                      </div>

                      {/* Income Stream Sub-Accounts and Readiness */}
                      <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div className="md:col-span-2 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                          <div className="flex justify-between items-center border-b pb-3">
                            <h4 className="font-bold text-slate-900 text-sm flex items-center gap-2">
                              <Award className="w-4 h-4 text-emerald-600" /> Income Stream Breakdown (Part 5 Locked Rules)
                            </h4>
                            <button
                              onClick={() => setPreviewView('income_user')}
                              className="text-xs text-blue-600 hover:underline font-semibold"
                            >
                              Detailed Report &rarr;
                            </button>
                          </div>

                          <div className="space-y-3">
                            <div className="p-3 rounded-xl bg-slate-50 border flex justify-between items-center">
                              <div>
                                <span className="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full mr-2">100%</span>
                                <strong className="text-slate-800 text-xs">Direct Income</strong>
                                <div className="text-[11px] text-slate-500">2 referrals &times; ₹1,200 Level 1 joinings</div>
                              </div>
                              <span className="font-mono font-bold text-emerald-600 text-base">₹{simWallet.directIncome.toLocaleString('en-IN')}</span>
                            </div>

                            <div className="p-3 rounded-xl bg-slate-50 border flex justify-between items-center">
                              <div>
                                <span className="bg-sky-100 text-sky-800 text-[10px] font-bold px-2 py-0.5 rounded-full mr-2">50%</span>
                                <strong className="text-slate-800 text-xs">Level Income (Level 2 &rarr; 7)</strong>
                                <div className="text-[11px] text-slate-500">Downline upgrade (requires member upgraded)</div>
                              </div>
                              <span className="font-mono font-bold text-sky-600 text-base">₹{simWallet.levelIncome.toLocaleString('en-IN')}</span>
                            </div>

                            <div className="p-3 rounded-xl bg-slate-50 border flex justify-between items-center">
                              <div>
                                <span className="bg-purple-100 text-purple-800 text-[10px] font-bold px-2 py-0.5 rounded-full mr-2">50%</span>
                                <strong className="text-slate-800 text-xs">Direct Sponsor Income (L2 &rarr; Unlimited)</strong>
                                <div className="text-[11px] text-slate-500">Earned from direct sponsor relationship</div>
                              </div>
                              <span className="font-mono font-bold text-purple-600 text-base">₹{simWallet.sponsorIncome.toLocaleString('en-IN')}</span>
                            </div>
                          </div>
                        </div>

                        {/* KYC & Payout Readiness Panel */}
                        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                          <h4 className="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <ShieldCheck className="w-4 h-4 text-emerald-600" /> Payout Terms &amp; KYC
                          </h4>

                          <div className="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs space-y-1">
                            <div className="flex items-center gap-1.5 text-emerald-800 font-bold">
                              <CheckCircle className="w-4 h-4 text-emerald-600" /> KYC Status: Approved
                            </div>
                            <div className="text-[11px] text-emerald-700">A/C: ****5678 (HDFC Bank) &bull; Verified</div>
                          </div>

                          <div className="text-xs space-y-2 border-t pt-3">
                            <div className="flex justify-between text-slate-600">
                              <span>Min Payout:</span>
                              <strong className="text-slate-900 font-mono">₹500.00</strong>
                            </div>
                            <div className="flex justify-between text-slate-600">
                              <span>Govt TDS (5%):</span>
                              <strong className="text-rose-600 font-mono">5%</strong>
                            </div>
                            <div className="flex justify-between text-slate-600">
                              <span>Admin Charge (5%):</span>
                              <strong className="text-rose-600 font-mono">5%</strong>
                            </div>
                            <div className="flex justify-between text-slate-900 font-bold border-t pt-2">
                              <span>Net Payable:</span>
                              <strong className="text-emerald-600 font-mono">90% Net</strong>
                            </div>
                          </div>

                          <button
                            onClick={() => setPreviewView('withdraw')}
                            className="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-xl text-xs transition flex items-center justify-center gap-1.5 shadow"
                          >
                            <DollarSign className="w-4 h-4" /> Go to Withdrawal Screen
                          </button>
                        </div>
                      </div>

                      {/* Recent Transactions Table with Balance Before & Balance After */}
                      <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                        <div className="p-4 border-b border-slate-200 flex justify-between items-center">
                          <div>
                            <h4 className="font-bold text-slate-900 text-sm flex items-center gap-2">
                              <CreditCard className="w-4 h-4 text-blue-600" /> Recent Wallet Ledger (Balance Before &amp; After)
                            </h4>
                            <p className="text-[11px] text-slate-400">Strict ledger logging as requested in Part 6</p>
                          </div>
                          <button
                            onClick={() => setPreviewView('transactions')}
                            className="text-xs text-blue-600 hover:underline font-semibold"
                          >
                            View Full Ledger &rarr;
                          </button>
                        </div>

                        <div className="overflow-x-auto">
                          <table className="w-full text-left border-collapse text-xs">
                            <thead>
                              <tr className="bg-slate-50 text-slate-500 text-[11px] border-b">
                                <th className="p-3 pl-4">Date &amp; Time</th>
                                <th className="p-3">Reference No</th>
                                <th className="p-3">Type</th>
                                <th className="p-3">Amount</th>
                                <th className="p-3">Balance Before</th>
                                <th className="p-3">Balance After</th>
                                <th className="p-3">Status</th>
                                <th className="p-3 pr-4">Description</th>
                              </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                              {simTransactions.slice(0, 4).map((tx) => (
                                <tr key={tx.id} className="hover:bg-slate-50 transition font-mono">
                                  <td className="p-3 pl-4 font-sans text-slate-500 text-[11px]">{tx.date}</td>
                                  <td className="p-3 font-bold text-slate-800">{tx.refNo}</td>
                                  <td className="p-3 font-sans">
                                    {tx.type === 'credit' ? (
                                      <span className="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full text-[10px] font-bold">Credit</span>
                                    ) : (
                                      <span className="bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full text-[10px] font-bold">Debit</span>
                                    )}
                                  </td>
                                  <td className={`p-3 font-bold ${tx.type === 'credit' ? 'text-emerald-600' : 'text-rose-600'}`}>
                                    {tx.type === 'credit' ? '+' : '-'}₹{tx.amount.toLocaleString('en-IN')}
                                  </td>
                                  <td className="p-3 text-slate-500">₹{tx.balanceBefore.toLocaleString('en-IN')}</td>
                                  <td className="p-3 font-bold text-slate-900">₹{tx.balanceAfter.toLocaleString('en-IN')}</td>
                                  <td className="p-3 font-sans">
                                    {tx.status === 'completed' && <span className="text-emerald-600 font-bold text-[10px]">Completed</span>}
                                    {tx.status === 'pending' && <span className="text-amber-500 font-bold text-[10px]">Pending</span>}
                                    {tx.status === 'failed' && <span className="text-rose-600 font-bold text-[10px]">Refunded</span>}
                                  </td>
                                  <td className="p-3 pr-4 font-sans text-slate-600 text-[11px] truncate max-w-xs">{tx.desc}</td>
                                </tr>
                              ))}
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    PART 6: USER WITHDRAWAL (user/withdraw.php)
                ==================================================== */}
                {previewView === 'withdraw' && (
                  <div className="bg-slate-50 p-6 text-slate-800">
                    <div className="max-w-5xl mx-auto space-y-6">
                      <div className="bg-gradient-to-r from-emerald-800 to-teal-900 text-white p-5 rounded-2xl shadow-sm flex flex-wrap justify-between items-center gap-4">
                        <div>
                          <div className="flex items-center gap-2 mb-1">
                            <span className="bg-emerald-400/20 text-emerald-200 text-xs px-2.5 py-0.5 rounded-full font-bold">
                              Direct Payout Channel
                            </span>
                            <span className="bg-white/20 text-white text-xs px-2.5 py-0.5 rounded-full font-bold">
                              Standard 10% Total Deductions
                            </span>
                          </div>
                          <h2 className="text-xl font-bold flex items-center gap-2">
                            <DollarSign className="w-5 h-5 text-emerald-300" /> Member Payout &amp; Withdrawal Console
                          </h2>
                          <p className="text-xs text-emerald-100 mt-1">
                            Submit payout requests safely. Funds are row-locked immediately to prevent double spending.
                          </p>
                        </div>

                        <div className="bg-white/10 px-4 py-2.5 rounded-xl border border-white/20 text-right">
                          <span className="text-[10px] text-emerald-200 uppercase font-bold block">Available Balance</span>
                          <span className="text-xl font-black font-mono text-white">₹{simWallet.balance.toLocaleString('en-IN')}</span>
                        </div>
                      </div>

                      {withdrawAlert && (
                        <div
                          className={`p-4 rounded-xl text-xs flex items-center justify-between gap-3 ${
                            withdrawAlert.type === 'success'
                              ? 'bg-emerald-50 text-emerald-800 border border-emerald-300'
                              : 'bg-rose-50 text-rose-800 border border-rose-300'
                          }`}
                        >
                          <div className="flex items-center gap-2">
                            {withdrawAlert.type === 'success' ? (
                              <CheckCircle className="w-4 h-4 text-emerald-600" />
                            ) : (
                              <AlertCircle className="w-4 h-4 text-rose-600" />
                            )}
                            <span>{withdrawAlert.msg}</span>
                          </div>
                          <button onClick={() => setWithdrawAlert(null)} className="text-slate-400 hover:text-slate-600">
                            <X className="w-4 h-4" />
                          </button>
                        </div>
                      )}

                      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        {/* Interactive Withdrawal Request Form */}
                        <div className="lg:col-span-6 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                          <h3 className="font-bold text-slate-900 text-sm flex items-center gap-2 border-b pb-3">
                            <DollarSign className="w-4 h-4 text-emerald-600" /> New Withdrawal Request
                          </h3>

                          <div className="space-y-4 text-xs">
                            <div>
                              <label className="font-bold text-slate-700 block mb-1">
                                Withdrawal Amount (₹) *
                              </label>
                              <div className="relative">
                                <span className="absolute left-3 top-2.5 font-bold text-slate-500">₹</span>
                                <input
                                  type="number"
                                  min={500}
                                  max={simWallet.balance}
                                  value={withdrawAmountInput}
                                  onChange={(e) => setWithdrawAmountInput(Math.max(0, Number(e.target.value)))}
                                  className="w-full pl-8 pr-3 py-2 border rounded-xl font-mono text-base font-bold text-slate-900 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none"
                                />
                              </div>
                              <div className="flex justify-between text-[11px] text-slate-400 mt-1">
                                <span>Min: ₹500.00</span>
                                <span>Max Available: ₹{simWallet.balance.toLocaleString('en-IN')}</span>
                              </div>
                            </div>

                            {/* Payment Mode Selector */}
                            <div>
                              <label className="font-bold text-slate-700 block mb-2">Disbursement Mode *</label>
                              <div className="grid grid-cols-2 gap-3">
                                <button
                                  type="button"
                                  onClick={() => setWithdrawModeInput('Bank Transfer')}
                                  className={`p-3 rounded-xl border text-left transition ${
                                    withdrawModeInput === 'Bank Transfer'
                                      ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 ring-2 ring-emerald-500/20'
                                      : 'border-slate-200 text-slate-600 hover:bg-slate-50'
                                  }`}
                                >
                                  <div className="font-bold text-xs flex items-center gap-1.5">
                                    <CreditCard className="w-4 h-4 text-blue-600" /> Bank Transfer
                                  </div>
                                  <div className="text-[10px] text-slate-500 mt-0.5">NEFT / RTGS / IMPS</div>
                                </button>

                                <button
                                  type="button"
                                  onClick={() => setWithdrawModeInput('UPI')}
                                  className={`p-3 rounded-xl border text-left transition ${
                                    withdrawModeInput === 'UPI'
                                      ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 ring-2 ring-emerald-500/20'
                                      : 'border-slate-200 text-slate-600 hover:bg-slate-50'
                                  }`}
                                >
                                  <div className="font-bold text-xs flex items-center gap-1.5">
                                    <Award className="w-4 h-4 text-emerald-600" /> UPI Transfer
                                  </div>
                                  <div className="text-[10px] text-slate-500 mt-0.5">VPA Instant Payout</div>
                                </button>
                              </div>
                            </div>

                            {/* Credentials Summary Box */}
                            <div className="p-3 bg-slate-50 rounded-xl border text-slate-600 text-[11px] space-y-1">
                              <span className="font-bold text-slate-700 block text-[10px] uppercase">
                                Registered Beneficiary Details:
                              </span>
                              {withdrawModeInput === 'Bank Transfer' ? (
                                <>
                                  <div>Bank: <strong>HDFC Bank</strong></div>
                                  <div>Account: <strong>9812345678</strong> &bull; IFSC: <strong>HDFC0001234</strong></div>
                                  <div>Holder: <strong>Rohit Verma</strong></div>
                                </>
                              ) : (
                                <div>UPI ID: <strong className="font-mono text-emerald-700">rohit@okhdfcbank</strong></div>
                              )}
                            </div>

                            {/* Live Deduction Breakdown Box */}
                            {(() => {
                              const amt = Number(withdrawAmountInput) || 0;
                              const tds = amt * 0.05;
                              const admin = amt * 0.05;
                              const net = Math.max(0, amt - (tds + admin));
                              return (
                                <div className="p-4 bg-slate-100 rounded-xl border space-y-1.5 font-mono text-xs">
                                  <div className="flex justify-between text-slate-600 font-sans">
                                    <span>Requested Gross:</span>
                                    <strong className="text-slate-900">₹{amt.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</strong>
                                  </div>
                                  <div className="flex justify-between text-rose-600 font-sans">
                                    <span>Govt TDS (5%):</span>
                                    <span>-₹{tds.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>
                                  </div>
                                  <div className="flex justify-between text-rose-600 font-sans border-b pb-1.5">
                                    <span>Admin Handling (5%):</span>
                                    <span>-₹{admin.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>
                                  </div>
                                  <div className="flex justify-between text-emerald-700 font-sans text-sm font-bold pt-1">
                                    <span>Net Disbursable (90%):</span>
                                    <span>₹{net.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>
                                  </div>
                                </div>
                              );
                            })()}

                            <button
                              type="button"
                              onClick={() => {
                                const amt = Number(withdrawAmountInput) || 0;
                                if (amt < 500) {
                                  setWithdrawAlert({ type: 'error', msg: 'Minimum withdrawal amount is ₹500.00' });
                                  return;
                                }
                                if (amt > simWallet.balance) {
                                  setWithdrawAlert({ type: 'error', msg: `Insufficient balance! Available spendable: ₹${simWallet.balance.toLocaleString('en-IN')}` });
                                  return;
                                }

                                const tds = amt * 0.05;
                                const adminFee = amt * 0.05;
                                const net = amt - (tds + adminFee);
                                const newRef = `WTH-20261014-` + Math.random().toString(36).substring(2, 8).toUpperCase();

                                // Update Wallet atomically with row locking
                                const newBal = simWallet.balance - amt;
                                setSimWallet({
                                  ...simWallet,
                                  balance: newBal,
                                  availableForWithdrawal: newBal,
                                  totalDebit: simWallet.totalDebit + amt,
                                  pendingWithdrawal: simWallet.pendingWithdrawal + amt,
                                });

                                // Add to Withdrawal list
                                setSimWithdrawals([
                                  {
                                    id: Date.now(),
                                    refNo: newRef,
                                    username: 'rohit99',
                                    fullName: 'Rohit Verma',
                                    mobile: '9876543210',
                                    level: 2,
                                    kycStatus: 'approved',
                                    amount: amt,
                                    tds,
                                    adminFee,
                                    netAmount: net,
                                    paymentMode: withdrawModeInput,
                                    bankDetails: withdrawModeInput === 'UPI' ? 'rohit@okhdfcbank' : 'HDFC Bank - A/C 9812345678',
                                    status: 'pending',
                                    date: 'Just now',
                                    remarks: '',
                                    utr: '',
                                  },
                                  ...simWithdrawals,
                                ]);

                                // Add to transaction ledger with Balance Before & Balance After
                                setSimTransactions([
                                  {
                                    id: Date.now() + 1,
                                    refNo: newRef,
                                    date: 'Just now',
                                    type: 'debit',
                                    category: 'withdrawal',
                                    amount: amt,
                                    balanceBefore: simWallet.balance,
                                    balanceAfter: newBal,
                                    status: 'pending',
                                    desc: `Withdrawal request of ₹${amt.toLocaleString('en-IN')} (Net ₹${net.toLocaleString('en-IN')} via ${withdrawModeInput})`,
                                  },
                                  ...simTransactions,
                                ]);

                                setWithdrawAlert({
                                  type: 'success',
                                  msg: `Withdrawal request of ₹${amt.toLocaleString('en-IN')} submitted successfully! Ref: ${newRef}. Net payout: ₹${net.toLocaleString('en-IN')}.`,
                                });
                              }}
                              className="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl text-xs transition shadow flex items-center justify-center gap-2"
                            >
                              <CheckCircle className="w-4 h-4" /> Confirm &amp; Submit Withdrawal Request
                            </button>
                          </div>
                        </div>

                        {/* Rules and Anti-Double-Spend Safeguards */}
                        <div className="lg:col-span-6 space-y-4">
                          <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3 text-xs">
                            <h4 className="font-bold text-slate-900 text-sm flex items-center gap-2">
                              <ShieldCheck className="w-4 h-4 text-emerald-600" /> Locked Withdrawal &amp; Refund Rules
                            </h4>

                            <ul className="space-y-2 text-slate-600 text-[11px] list-disc pl-4">
                              <li>
                                <strong>Row-Locking Security:</strong> System locks the wallet row with <code>SELECT ... FOR UPDATE</code> during withdrawal to prevent simultaneous double-spend attacks.
                              </li>
                              <li>
                                <strong>Deductions Rule:</strong> Exactly 5% TDS + 5% Admin handling (10% total) deducted automatically from gross amount. Member receives 90% net.
                              </li>
                              <li>
                                <strong>Auto-Refund Guarantee:</strong> If the admin rejects your request (e.g. invalid account details), the locked ₹ amount is <strong>automatically credited back</strong> to your wallet balance with a <code>REFUND</code> ledger transaction!
                              </li>
                            </ul>

                            <div className="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-[11px] flex items-center gap-2">
                              <AlertTriangle className="w-4 h-4 text-amber-600 shrink-0" />
                              <span>Minimum withdrawal is ₹500. Ensure your bank IFSC is accurate before requesting.</span>
                            </div>
                          </div>

                          <div className="bg-gradient-to-r from-slate-900 to-slate-800 text-white p-5 rounded-2xl border border-slate-700 space-y-2 text-xs">
                            <div className="font-bold text-emerald-400">Admin Live Test Hint:</div>
                            <p className="text-slate-300 text-[11px]">
                              Switch to <strong>Admin Withdrawals (admin/withdrawals.php)</strong> in the top navigation to test approving or rejecting pending requests with live auto-refund into the member wallet!
                            </p>
                          </div>
                        </div>
                      </div>

                      {/* My Withdrawal History Table */}
                      <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                        <div className="p-4 border-b border-slate-200 flex justify-between items-center">
                          <h4 className="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <History className="w-4 h-4 text-emerald-600" /> My Withdrawal History
                          </h4>
                          <span className="text-xs text-slate-400">Total: {simWithdrawals.length} records</span>
                        </div>

                        <div className="overflow-x-auto">
                          <table className="w-full text-left border-collapse text-xs">
                            <thead>
                              <tr className="bg-slate-50 text-slate-500 text-[11px] border-b">
                                <th className="p-3 pl-4">Date</th>
                                <th className="p-3">Reference ID</th>
                                <th className="p-3">Gross</th>
                                <th className="p-3">TDS (5%)</th>
                                <th className="p-3">Admin (5%)</th>
                                <th className="p-3">Net Payable</th>
                                <th className="p-3">Mode</th>
                                <th className="p-3">Status</th>
                                <th className="p-3 pr-4">Remarks / UTR</th>
                              </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                              {simWithdrawals.map((w) => (
                                <tr key={w.id} className="hover:bg-slate-50 transition font-mono">
                                  <td className="p-3 pl-4 font-sans text-slate-500 text-[11px]">{w.date}</td>
                                  <td className="p-3 font-bold text-slate-800">{w.refNo}</td>
                                  <td className="p-3 font-bold text-slate-900">₹{w.amount.toLocaleString('en-IN')}</td>
                                  <td className="p-3 text-rose-500">-₹{w.tds.toLocaleString('en-IN')}</td>
                                  <td className="p-3 text-rose-500">-₹{w.adminFee.toLocaleString('en-IN')}</td>
                                  <td className="p-3 font-bold text-emerald-600 text-sm">₹{w.netAmount.toLocaleString('en-IN')}</td>
                                  <td className="p-3 font-sans text-slate-600">{w.paymentMode}</td>
                                  <td className="p-3 font-sans">
                                    {w.status === 'approved' && (
                                      <span className="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full text-[10px] font-bold">Approved</span>
                                    )}
                                    {w.status === 'pending' && (
                                      <span className="bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full text-[10px] font-bold">Pending</span>
                                    )}
                                    {w.status === 'rejected' && (
                                      <span className="bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full text-[10px] font-bold">Rejected (Refunded)</span>
                                    )}
                                  </td>
                                  <td className="p-3 pr-4 font-sans text-slate-500 text-[11px]">
                                    {w.utr ? <span className="font-mono text-emerald-700 block font-bold">{w.utr}</span> : null}
                                    {w.remarks || '—'}
                                  </td>
                                </tr>
                              ))}
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    PART 6: USER TRANSACTION LEDGER (user/transactions.php)
                ==================================================== */}
                {previewView === 'transactions' && (
                  <div className="bg-slate-50 p-6 text-slate-800">
                    <div className="max-w-5xl mx-auto space-y-6">
                      <div className="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-950 text-white p-5 rounded-2xl shadow-sm flex flex-wrap justify-between items-center gap-4">
                        <div>
                          <div className="flex items-center gap-2 mb-1">
                            <span className="bg-blue-400/20 text-blue-200 text-xs px-2.5 py-0.5 rounded-full font-bold">
                              Immutable Ledger Audit
                            </span>
                            <span className="bg-emerald-400/20 text-emerald-200 text-xs px-2.5 py-0.5 rounded-full font-bold">
                              Balance Before / After
                            </span>
                          </div>
                          <h2 className="text-xl font-bold flex items-center gap-2">
                            <CreditCard className="w-5 h-5 text-blue-300" /> Account Transaction Ledger (user/transactions.php)
                          </h2>
                          <p className="text-xs text-blue-100 mt-1">
                            Complete historical ledger. Every transaction records both Balance Before and Balance After.
                          </p>
                        </div>

                        {/* Filter Tabs: All, Income, Withdrawal, Refund */}
                        <div className="flex items-center gap-1.5 bg-white/10 p-1.5 rounded-xl border border-white/20 text-xs">
                          {(['all', 'income', 'withdrawal', 'refund'] as const).map((filterKey) => (
                            <button
                              key={filterKey}
                              onClick={() => setTxFilter(filterKey)}
                              className={`px-3 py-1 rounded-lg capitalize font-bold transition ${
                                txFilter === filterKey ? 'bg-white text-slate-950 shadow' : 'text-white/80 hover:text-white'
                              }`}
                            >
                              {filterKey}
                            </button>
                          ))}
                        </div>
                      </div>

                      {/* Transaction Table */}
                      <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                        <div className="p-4 border-b border-slate-200 flex justify-between items-center">
                          <h4 className="font-bold text-slate-900 text-sm">
                            Ledger Entries ({simTransactions.filter((tx) => {
                              if (txFilter === 'all') return true;
                              if (txFilter === 'income') return tx.category.includes('income');
                              if (txFilter === 'withdrawal') return tx.category === 'withdrawal';
                              if (txFilter === 'refund') return tx.desc.toLowerCase().includes('refund');
                              return true;
                            }).length})
                          </h4>
                          <span className="text-[11px] text-slate-400">Strictly tracks balance before and after</span>
                        </div>

                        <div className="overflow-x-auto">
                          <table className="w-full text-left border-collapse text-xs">
                            <thead>
                              <tr className="bg-slate-50 text-slate-500 text-[11px] border-b">
                                <th className="p-3 pl-4">Date</th>
                                <th className="p-3">Transaction ID</th>
                                <th className="p-3">Type</th>
                                <th className="p-3">Amount</th>
                                <th className="p-3">Balance Before</th>
                                <th className="p-3">Balance After</th>
                                <th className="p-3">Status</th>
                                <th className="p-3 pr-4">Description</th>
                              </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 font-mono">
                              {simTransactions
                                .filter((tx) => {
                                  if (txFilter === 'all') return true;
                                  if (txFilter === 'income') return tx.category.includes('income');
                                  if (txFilter === 'withdrawal') return tx.category === 'withdrawal';
                                  if (txFilter === 'refund') return tx.desc.toLowerCase().includes('refund');
                                  return true;
                                })
                                .map((tx) => (
                                  <tr key={tx.id} className="hover:bg-slate-50 transition">
                                    <td className="p-3 pl-4 font-sans text-slate-500 text-[11px]">{tx.date}</td>
                                    <td className="p-3 font-bold text-slate-800">{tx.refNo}</td>
                                    <td className="p-3 font-sans">
                                      {tx.type === 'credit' ? (
                                        <span className="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full text-[10px] font-bold">Credit</span>
                                      ) : (
                                        <span className="bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full text-[10px] font-bold">Debit</span>
                                      )}
                                    </td>
                                    <td className={`p-3 font-bold ${tx.type === 'credit' ? 'text-emerald-600' : 'text-rose-600'}`}>
                                      {tx.type === 'credit' ? '+' : '-'}₹{tx.amount.toLocaleString('en-IN')}
                                    </td>
                                    <td className="p-3 text-slate-500">₹{tx.balanceBefore.toLocaleString('en-IN')}</td>
                                    <td className="p-3 font-bold text-slate-900">₹{tx.balanceAfter.toLocaleString('en-IN')}</td>
                                    <td className="p-3 font-sans">
                                      {tx.status === 'completed' && <span className="text-emerald-600 font-bold text-[10px]">Completed</span>}
                                      {tx.status === 'pending' && <span className="text-amber-500 font-bold text-[10px]">Pending</span>}
                                      {tx.status === 'failed' && <span className="text-rose-600 font-bold text-[10px]">Failed / Refunded</span>}
                                    </td>
                                    <td className="p-3 pr-4 font-sans text-slate-600 text-[11px]">{tx.desc}</td>
                                  </tr>
                                ))}
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    PART 6: ADMIN WITHDRAWALS CONSOLE (admin/withdrawals.php)
                ==================================================== */}
                {previewView === 'admin_withdrawals' && (
                  <div className="bg-slate-50 p-6 text-slate-800">
                    <div className="max-w-5xl mx-auto space-y-6">
                      <div className="bg-gradient-to-r from-amber-700 via-amber-800 to-slate-950 text-white p-5 rounded-2xl shadow-sm flex flex-wrap justify-between items-center gap-4">
                        <div>
                          <div className="flex items-center gap-2 mb-1">
                            <span className="bg-amber-400/20 text-amber-200 text-xs px-2.5 py-0.5 rounded-full font-bold">
                              Operator Control Center
                            </span>
                            <span className="bg-emerald-400/20 text-emerald-200 text-xs px-2.5 py-0.5 rounded-full font-bold">
                              Anti-Fraud Verification
                            </span>
                          </div>
                          <h2 className="text-xl font-bold flex items-center gap-2">
                            <ShieldCheck className="w-5 h-5 text-amber-300" /> Admin Payout &amp; Withdrawal Management (admin/withdrawals.php)
                          </h2>
                          <p className="text-xs text-amber-100 mt-1">
                            Review member KYC, verify bank credentials, disburse net payouts, or reject with automatic wallet refund.
                          </p>
                        </div>

                        <div className="flex items-center gap-2">
                          <span className="bg-white/10 px-3 py-1.5 rounded-xl border border-white/20 text-xs font-mono">
                            Pending Requests: {simWithdrawals.filter((w) => w.status === 'pending').length}
                          </span>
                        </div>
                      </div>

                      {adminActionAlert && (
                        <div
                          className={`p-4 rounded-xl text-xs flex items-center justify-between gap-3 ${
                            adminActionAlert.type === 'success'
                              ? 'bg-emerald-50 text-emerald-800 border border-emerald-300'
                              : 'bg-rose-50 text-rose-800 border border-rose-300'
                          }`}
                        >
                          <div className="flex items-center gap-2">
                            {adminActionAlert.type === 'success' ? (
                              <CheckCircle className="w-4 h-4 text-emerald-600" />
                            ) : (
                              <AlertCircle className="w-4 h-4 text-rose-600" />
                            )}
                            <span>{adminActionAlert.msg}</span>
                          </div>
                          <button onClick={() => setAdminActionAlert(null)} className="text-slate-400 hover:text-slate-600">
                            <X className="w-4 h-4" />
                          </button>
                        </div>
                      )}

                      {/* Modal for Approve or Reject */}
                      {selectedWithdrawalForAction && (
                        <div className="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
                          <div className="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden text-xs">
                            <div className={`p-4 text-white font-bold text-sm flex justify-between items-center ${actionType === 'approve' ? 'bg-emerald-600' : 'bg-rose-600'}`}>
                              <span>{actionType === 'approve' ? 'Approve & Disburse Payout' : 'Reject & Auto-Refund to Wallet'}</span>
                              <button onClick={() => setSelectedWithdrawalForAction(null)} className="text-white hover:opacity-80">
                                <X className="w-4 h-4" />
                              </button>
                            </div>

                            <div className="p-5 space-y-4">
                              {(() => {
                                const target = simWithdrawals.find((w) => w.id === selectedWithdrawalForAction);
                                if (!target) return null;
                                return (
                                  <>
                                    <div className="p-3 bg-slate-50 rounded-xl border space-y-1">
                                      <div className="flex justify-between">
                                        <span className="text-slate-500">Member:</span>
                                        <strong>{target.fullName} (@{target.username})</strong>
                                      </div>
                                      <div className="flex justify-between">
                                        <span className="text-slate-500">Gross Amount:</span>
                                        <strong className="font-mono">₹{target.amount.toLocaleString('en-IN')}</strong>
                                      </div>
                                      <div className="flex justify-between text-rose-600">
                                        <span>TDS (5%) + Admin (5%):</span>
                                        <strong className="font-mono">-₹{(target.tds + target.adminFee).toLocaleString('en-IN')}</strong>
                                      </div>
                                      <div className="flex justify-between border-t pt-1 font-bold">
                                        <span className="text-slate-900">Net Disbursable:</span>
                                        <strong className="text-emerald-600 font-mono text-sm">₹{target.netAmount.toLocaleString('en-IN')}</strong>
                                      </div>
                                      <div className="text-[10px] text-slate-500 pt-1">
                                        Target: {target.bankDetails} ({target.paymentMode})
                                      </div>
                                    </div>

                                    {actionType === 'approve' ? (
                                      <div className="space-y-2">
                                        <label className="font-bold text-slate-700 block">Bank UTR / Payout Reference *</label>
                                        <input
                                          type="text"
                                          placeholder="e.g. UTR-9821092817"
                                          value={adminUtrInput}
                                          onChange={(e) => setAdminUtrInput(e.target.value)}
                                          className="w-full border p-2 rounded-lg font-mono"
                                        />
                                        <label className="font-bold text-slate-700 block mt-2">Admin Remarks</label>
                                        <textarea
                                          rows={2}
                                          placeholder="Disbursed successfully via NEFT/UPI."
                                          value={adminRemarksInput}
                                          onChange={(e) => setAdminRemarksInput(e.target.value)}
                                          className="w-full border p-2 rounded-lg"
                                        />
                                      </div>
                                    ) : (
                                      <div className="space-y-2">
                                        <div className="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-[11px]">
                                          Rejecting this request will <strong>instantly refund ₹{target.amount.toLocaleString('en-IN')}</strong> back to the member's wallet balance with an official REFUND transaction!
                                        </div>
                                        <label className="font-bold text-slate-700 block">Rejection Reason *</label>
                                        <textarea
                                          rows={3}
                                          placeholder="e.g. Invalid bank account number or mismatching IFSC code. Please update profile."
                                          value={adminRemarksInput}
                                          onChange={(e) => setAdminRemarksInput(e.target.value)}
                                          className="w-full border p-2 rounded-lg"
                                        />
                                      </div>
                                    )}

                                    <div className="flex justify-end gap-2 pt-2 border-t">
                                      <button
                                        type="button"
                                        onClick={() => setSelectedWithdrawalForAction(null)}
                                        className="px-3 py-1.5 rounded-lg border text-slate-600 hover:bg-slate-100"
                                      >
                                        Cancel
                                      </button>
                                      <button
                                        type="button"
                                        onClick={() => {
                                          if (actionType === 'approve') {
                                            const utr = adminUtrInput.trim() || 'UTR-' + Math.random().toString().slice(2, 12);
                                            // Update withdrawal record
                                            setSimWithdrawals(
                                              simWithdrawals.map((item) =>
                                                item.id === target.id
                                                  ? { ...item, status: 'approved', utr, remarks: adminRemarksInput || 'Approved & Disbursed' }
                                                  : item
                                              )
                                            );
                                            // Update wallet
                                            setSimWallet({
                                              ...simWallet,
                                              pendingWithdrawal: Math.max(0, simWallet.pendingWithdrawal - target.amount),
                                              totalWithdrawn: simWallet.totalWithdrawn + target.amount,
                                            });
                                            // Update transaction status
                                            setSimTransactions(
                                              simTransactions.map((tx) =>
                                                tx.refNo === target.refNo ? { ...tx, status: 'completed' } : tx
                                              )
                                            );
                                            setAdminActionAlert({
                                              type: 'success',
                                              msg: `Withdrawal ${target.refNo} successfully approved! UTR: ${utr}.`,
                                            });
                                          } else {
                                            if (!adminRemarksInput.trim()) {
                                              alert('Please provide a rejection reason');
                                              return;
                                            }
                                            // Update withdrawal record
                                            setSimWithdrawals(
                                              simWithdrawals.map((item) =>
                                                item.id === target.id
                                                  ? { ...item, status: 'rejected', remarks: adminRemarksInput }
                                                  : item
                                              )
                                            );
                                            // Refund funds back to member wallet
                                            const prevBal = simWallet.balance;
                                            const refundedBal = prevBal + target.amount;
                                            setSimWallet({
                                              ...simWallet,
                                              balance: refundedBal,
                                              availableForWithdrawal: refundedBal,
                                              totalDebit: Math.max(0, simWallet.totalDebit - target.amount),
                                              pendingWithdrawal: Math.max(0, simWallet.pendingWithdrawal - target.amount),
                                            });
                                            // Add Refund transaction ledger entry
                                            const refundRef = 'REF-' + Math.random().toString(36).substring(2, 8).toUpperCase();
                                            setSimTransactions([
                                              {
                                                id: Date.now(),
                                                refNo: refundRef,
                                                date: 'Just now',
                                                type: 'credit',
                                                category: 'admin_adjustment',
                                                amount: target.amount,
                                                balanceBefore: prevBal,
                                                balanceAfter: refundedBal,
                                                status: 'completed',
                                                desc: `Refund of rejected withdrawal ${target.refNo}. Reason: ${adminRemarksInput}`,
                                              },
                                              ...simTransactions.map((tx) =>
                                                tx.refNo === target.refNo ? { ...tx, status: 'failed' } : tx
                                              ),
                                            ]);
                                            setAdminActionAlert({
                                              type: 'success',
                                              msg: `Withdrawal ${target.refNo} rejected! ₹${target.amount.toLocaleString('en-IN')} has been safely refunded to member wallet.`,
                                            });
                                          }
                                          setSelectedWithdrawalForAction(null);
                                          setAdminRemarksInput('');
                                          setAdminUtrInput('');
                                        }}
                                        className={`px-4 py-1.5 rounded-lg text-white font-bold ${actionType === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'}`}
                                      >
                                        {actionType === 'approve' ? 'Confirm Approval' : 'Confirm Rejection & Refund'}
                                      </button>
                                    </div>
                                  </>
                                );
                              })()}
                            </div>
                          </div>
                        </div>
                      )}

                      {/* Admin Table */}
                      <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                        <div className="p-4 border-b border-slate-200 flex justify-between items-center">
                          <h4 className="font-bold text-slate-900 text-sm">
                            Platform Withdrawal Requests ({simWithdrawals.length})
                          </h4>
                          <span className="text-xs text-slate-400">Click Approve or Reject below</span>
                        </div>

                        <div className="overflow-x-auto">
                          <table className="w-full text-left border-collapse text-xs">
                            <thead>
                              <tr className="bg-slate-50 text-slate-500 text-[11px] border-b">
                                <th className="p-3 pl-4">Ref / Date</th>
                                <th className="p-3">Member</th>
                                <th className="p-3">KYC</th>
                                <th className="p-3">Gross</th>
                                <th className="p-3">Deductions</th>
                                <th className="p-3">Net Disbursable</th>
                                <th className="p-3">Payment Credentials</th>
                                <th className="p-3">Status</th>
                                <th className="p-3 pr-4 text-end">Action</th>
                              </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                              {simWithdrawals.map((w) => (
                                <tr key={w.id} className="hover:bg-slate-50 transition">
                                  <td className="p-3 pl-4 font-mono font-bold text-slate-800">
                                    {w.refNo}
                                    <div className="text-[10px] font-sans text-slate-400">{w.date}</div>
                                  </td>
                                  <td className="p-3">
                                    <div className="font-bold text-slate-900">{w.fullName}</div>
                                    <div className="text-[10px] text-slate-500 font-mono">@{w.username} &bull; L{w.level}</div>
                                  </td>
                                  <td className="p-3">
                                    <span className="bg-emerald-100 text-emerald-800 text-[10px] px-2 py-0.5 rounded-full font-bold">
                                      KYC Approved
                                    </span>
                                  </td>
                                  <td className="p-3 font-mono font-bold text-slate-900">
                                    ₹{w.amount.toLocaleString('en-IN')}
                                  </td>
                                  <td className="p-3 font-mono text-rose-500 text-[11px]">
                                    -₹{(w.tds + w.adminFee).toLocaleString('en-IN')}
                                  </td>
                                  <td className="p-3 font-mono font-bold text-emerald-600 text-sm">
                                    ₹{w.netAmount.toLocaleString('en-IN')}
                                  </td>
                                  <td className="p-3 text-[11px] text-slate-600 max-w-xs">
                                    <div className="font-bold text-slate-800">{w.paymentMode}</div>
                                    <div className="font-mono text-slate-500 truncate">{w.bankDetails}</div>
                                  </td>
                                  <td className="p-3">
                                    {w.status === 'approved' && (
                                      <span className="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full text-[10px] font-bold">Approved</span>
                                    )}
                                    {w.status === 'pending' && (
                                      <span className="bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full text-[10px] font-bold">Pending</span>
                                    )}
                                    {w.status === 'rejected' && (
                                      <span className="bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full text-[10px] font-bold">Rejected</span>
                                    )}
                                  </td>
                                  <td className="p-3 pr-4 text-end">
                                    {w.status === 'pending' ? (
                                      <div className="flex justify-end gap-1.5">
                                        <button
                                          onClick={() => {
                                            setSelectedWithdrawalForAction(w.id);
                                            setActionType('approve');
                                          }}
                                          className="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2.5 py-1 rounded text-[11px] transition"
                                        >
                                          Approve
                                        </button>
                                        <button
                                          onClick={() => {
                                            setSelectedWithdrawalForAction(w.id);
                                            setActionType('reject');
                                          }}
                                          className="bg-rose-600 hover:bg-rose-700 text-white font-bold px-2.5 py-1 rounded text-[11px] transition"
                                        >
                                          Reject
                                        </button>
                                      </div>
                                    ) : (
                                      <span className="text-[10px] text-slate-400 font-mono">
                                        {w.status === 'approved' ? (w.utr || 'Disbursed') : 'Refunded'}
                                      </span>
                                    )}
                                  </td>
                                </tr>
                              ))}
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    1. BINARY TREE VISUALIZATION (Part 4 Core)
                ==================================================== */}
                {previewView === 'binary_tree' && activeTreeData && (
                  <div className="bg-slate-50 p-6 text-slate-800">
                    <div className="max-w-5xl mx-auto space-y-5">
                      {/* Banner */}
                      <div className="bg-gradient-to-r from-slate-950 to-slate-800 text-white p-5 rounded-2xl shadow-sm flex flex-wrap justify-between items-center gap-4">
                        <div>
                          <div className="flex items-center gap-2 mb-1">
                            <span className="bg-emerald-500/20 text-emerald-300 text-xs px-2.5 py-0.5 rounded-full font-bold">
                              2&times;2 Binary Architecture
                            </span>
                            <span className="bg-sky-500/20 text-sky-300 text-xs px-2.5 py-0.5 rounded-full font-bold">
                              Viewing: @{activeTreeData.root.username}
                            </span>
                            {treeRootId !== 1 && (
                              <button
                                onClick={() => setTreeRootId(1)}
                                className="bg-amber-400 text-slate-950 text-xs px-3 py-0.5 rounded-full font-bold hover:bg-amber-300 transition"
                              >
                                &larr; Return to My Root
                              </button>
                            )}
                          </div>
                          <h2 className="text-xl font-bold">Visual Binary Network</h2>
                          <p className="text-xs text-slate-400">
                            Each node has maximum 2 direct children (Left &amp; Right). Click any member card to drill down into their sub-network.
                          </p>
                        </div>

                        {/* Zoom Controls */}
                        <div className="flex items-center gap-1.5 bg-white/10 p-2 rounded-xl border border-white/10 text-xs">
                          <span className="text-slate-400 text-[11px] mr-1">Zoom:</span>
                          <button
                            onClick={() => setTreeZoom(Math.min(treeZoom + 0.15, 1.45))}
                            className="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded-lg"
                            title="Zoom In"
                          >
                            <ZoomIn className="w-3.5 h-3.5" />
                          </button>
                          <button
                            onClick={() => setTreeZoom(Math.max(treeZoom - 0.15, 0.65))}
                            className="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded-lg"
                            title="Zoom Out"
                          >
                            <ZoomOut className="w-3.5 h-3.5" />
                          </button>
                          <button
                            onClick={() => setTreeZoom(1)}
                            className="bg-white/20 hover:bg-white/30 text-white px-2 py-1.5 rounded-lg text-[10px]"
                            title="Reset"
                          >
                            Reset
                          </button>
                        </div>
                      </div>

                      {/* Tree Breadcrumbs */}
                      {treeRootId !== 1 && (
                        <div className="bg-white p-3 rounded-xl border border-slate-200 text-xs flex items-center gap-2 text-slate-600">
                          <span className="font-semibold text-slate-400">Network Path:</span>
                          <button onClick={() => setTreeRootId(1)} className="text-blue-600 hover:underline font-bold">
                            @ramesh99 (You)
                          </button>
                          <ChevronRight className="w-3.5 h-3.5 text-slate-400" />
                          <span className="font-bold text-slate-900">@{activeTreeData.root.username}</span>
                        </div>
                      )}

                      {/* Visual Tree Canvas */}
                      <div className="bg-white rounded-2xl border border-slate-200 p-6 overflow-x-auto shadow-sm text-center min-h-[460px]">
                        <div
                          style={{
                            transform: `scale(${treeZoom})`,
                            transformOrigin: 'top center',
                            transition: 'transform 0.2s ease',
                            display: 'inline-block',
                            minWidth: '780px',
                          }}
                          className="py-4"
                        >
                          {/* TIER 1: ROOT */}
                          <div className="flex justify-center mb-6">
                            <div
                              onClick={() => handleDrilldown(activeTreeData.root.id)}
                              className="bg-white border-2 border-blue-600 p-3 rounded-2xl shadow-md w-44 text-center cursor-pointer hover:shadow-lg transition"
                            >
                              <div className="w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center mx-auto mb-1 text-xs">
                                {activeTreeData.root.fullName.charAt(0)}
                              </div>
                              <div className="font-bold text-xs text-slate-900 truncate">{activeTreeData.root.fullName}</div>
                              <div className="text-[10px] font-mono text-slate-500">@{activeTreeData.root.username}</div>
                              <div className="flex justify-center gap-1 mt-1.5">
                                <span className="bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                  Level {activeTreeData.root.level}
                                </span>
                                <span className="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                  Active
                                </span>
                              </div>
                            </div>
                          </div>

                          {/* CONNECTOR LINE T1 -> T2 */}
                          <div className="relative h-6 mb-4">
                            <div className="absolute left-1/2 top-0 w-0.5 h-3 bg-blue-600 -translate-x-1/2"></div>
                            <div className="absolute left-1/4 right-1/4 top-3 h-0.5 bg-blue-600"></div>
                            <div className="absolute left-1/4 top-3 w-0.5 h-3 bg-blue-600"></div>
                            <div className="absolute right-1/4 top-3 w-0.5 h-3 bg-blue-600"></div>
                          </div>

                          {/* TIER 2: 2 CHILDREN (LEFT & RIGHT) */}
                          <div className="flex justify-around mb-6">
                            {/* Left Child */}
                            <div className="w-1/2 flex flex-col items-center">
                              {activeTreeData.left ? (
                                <div
                                  onClick={() => handleDrilldown(activeTreeData.left!.id)}
                                  className="bg-white border-2 border-sky-500 p-3 rounded-2xl shadow-sm w-44 text-center cursor-pointer hover:shadow-md transition relative"
                                >
                                  <span className="absolute -top-2 left-1/2 -translate-x-1/2 bg-blue-600 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">
                                    Left
                                  </span>
                                  <div className="w-8 h-8 rounded-full bg-sky-100 text-sky-700 font-bold flex items-center justify-center mx-auto mb-1 text-xs">
                                    {activeTreeData.left.fullName.charAt(0)}
                                  </div>
                                  <div className="font-bold text-xs text-slate-900 truncate">{activeTreeData.left.fullName}</div>
                                  <div className="text-[10px] font-mono text-slate-500">@{activeTreeData.left.username}</div>
                                  <div className="flex justify-center gap-1 mt-1.5">
                                    <span className="bg-sky-100 text-sky-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                      Level {activeTreeData.left.level}
                                    </span>
                                  </div>
                                </div>
                              ) : (
                                <div className="border-2 border-dashed border-slate-300 bg-slate-50/60 p-4 rounded-2xl w-44 text-center text-slate-400">
                                  <div className="text-xs font-semibold">Available</div>
                                  <span className="text-[10px] uppercase text-slate-400">Left Slot</span>
                                </div>
                              )}
                            </div>

                            {/* Right Child */}
                            <div className="w-1/2 flex flex-col items-center">
                              {activeTreeData.right ? (
                                <div
                                  onClick={() => handleDrilldown(activeTreeData.right!.id)}
                                  className="bg-white border-2 border-indigo-500 p-3 rounded-2xl shadow-sm w-44 text-center cursor-pointer hover:shadow-md transition relative"
                                >
                                  <span className="absolute -top-2 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">
                                    Right
                                  </span>
                                  <div className="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center mx-auto mb-1 text-xs">
                                    {activeTreeData.right.fullName.charAt(0)}
                                  </div>
                                  <div className="font-bold text-xs text-slate-900 truncate">{activeTreeData.right.fullName}</div>
                                  <div className="text-[10px] font-mono text-slate-500">@{activeTreeData.right.username}</div>
                                  <div className="flex justify-center gap-1 mt-1.5">
                                    <span className="bg-indigo-100 text-indigo-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                      Level {activeTreeData.right.level}
                                    </span>
                                  </div>
                                </div>
                              ) : (
                                <div className="border-2 border-dashed border-slate-300 bg-slate-50/60 p-4 rounded-2xl w-44 text-center text-slate-400">
                                  <div className="text-xs font-semibold">Available</div>
                                  <span className="text-[10px] uppercase text-slate-400">Right Slot</span>
                                </div>
                              )}
                            </div>
                          </div>

                          {/* CONNECTOR LINE T2 -> T3 */}
                          <div className="flex justify-around mb-4">
                            <div className="w-1/2 relative h-6">
                              <div className="absolute left-1/2 top-0 w-0.5 h-3 bg-slate-300 -translate-x-1/2"></div>
                              <div className="absolute left-1/4 right-1/4 top-3 h-0.5 bg-slate-300"></div>
                              <div className="absolute left-1/4 top-3 w-0.5 h-3 bg-slate-300"></div>
                              <div className="absolute right-1/4 top-3 w-0.5 h-3 bg-slate-300"></div>
                            </div>
                            <div className="w-1/2 relative h-6">
                              <div className="absolute left-1/2 top-0 w-0.5 h-3 bg-slate-300 -translate-x-1/2"></div>
                              <div className="absolute left-1/4 right-1/4 top-3 h-0.5 bg-slate-300"></div>
                              <div className="absolute left-1/4 top-3 w-0.5 h-3 bg-slate-300"></div>
                              <div className="absolute right-1/4 top-3 w-0.5 h-3 bg-slate-300"></div>
                            </div>
                          </div>

                          {/* TIER 3: 4 GRANDCHILDREN */}
                          <div className="flex justify-between">
                            {/* LL */}
                            <div className="w-1/4 flex justify-center">
                              {activeTreeData.left?.left ? (
                                <div
                                  onClick={() => handleDrilldown(activeTreeData.left!.left!.id)}
                                  className="bg-white border p-2.5 rounded-xl shadow-xs w-36 text-center cursor-pointer hover:border-blue-400 transition"
                                >
                                  <span className="text-[9px] bg-blue-100 text-blue-800 font-bold px-1.5 py-0.2 rounded">L</span>
                                  <div className="font-bold text-[11px] text-slate-900 truncate mt-1">{activeTreeData.left.left.fullName}</div>
                                  <div className="text-[9px] font-mono text-slate-500">@{activeTreeData.left.left.username}</div>
                                </div>
                              ) : (
                                <div className="border border-dashed border-slate-300 bg-slate-50/40 p-2.5 rounded-xl w-36 text-center text-slate-400 text-[10px]">
                                  Available
                                </div>
                              )}
                            </div>

                            {/* LR */}
                            <div className="w-1/4 flex justify-center">
                              {activeTreeData.left?.right ? (
                                <div className="bg-white border p-2.5 rounded-xl shadow-xs w-36 text-center">
                                  <span className="text-[9px] bg-indigo-100 text-indigo-800 font-bold px-1.5 py-0.2 rounded">R</span>
                                  <div className="font-bold text-[11px] truncate mt-1">{activeTreeData.left.right.fullName}</div>
                                </div>
                              ) : (
                                <div className="border border-dashed border-slate-300 bg-slate-50/40 p-2.5 rounded-xl w-36 text-center text-slate-400 text-[10px]">
                                  Available
                                </div>
                              )}
                            </div>

                            {/* RL */}
                            <div className="w-1/4 flex justify-center">
                              {activeTreeData.right?.left ? (
                                <div
                                  onClick={() => handleDrilldown(activeTreeData.right!.left!.id)}
                                  className="bg-white border p-2.5 rounded-xl shadow-xs w-36 text-center cursor-pointer hover:border-blue-400 transition"
                                >
                                  <span className="text-[9px] bg-blue-100 text-blue-800 font-bold px-1.5 py-0.2 rounded">L</span>
                                  <div className="font-bold text-[11px] text-slate-900 truncate mt-1">{activeTreeData.right.left.fullName}</div>
                                  <div className="text-[9px] font-mono text-slate-500">@{activeTreeData.right.left.username}</div>
                                </div>
                              ) : (
                                <div className="border border-dashed border-slate-300 bg-slate-50/40 p-2.5 rounded-xl w-36 text-center text-slate-400 text-[10px]">
                                  Available
                                </div>
                              )}
                            </div>

                            {/* RR */}
                            <div className="w-1/4 flex justify-center">
                              <div className="border border-dashed border-slate-300 bg-slate-50/40 p-2.5 rounded-xl w-36 text-center text-slate-400 text-[10px]">
                                Available
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      {/* Rule Compliance Notice */}
                      <div className="bg-blue-50 border border-blue-200 p-4 rounded-xl text-blue-900 text-xs">
                        <strong>2&times;2 Binary Integrity:</strong>
                        <p className="mt-1 text-slate-600">
                          Each parent node has maximum 2 direct legs (Left and Right). Duplicate positions are rejected at both database and PHP levels.
                          As strictly instructed in Part 4, <strong>zero income calculations are executed</strong>.
                        </p>
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    2. MY TEAM PAGE (Part 4 Core)
                ==================================================== */}
                {previewView === 'team' && (
                  <div className="bg-slate-50 p-6 text-slate-800">
                    <div className="max-w-5xl mx-auto space-y-5">
                      <div className="flex justify-between items-center">
                        <div>
                          <h2 className="text-xl font-bold text-slate-900">My Team Management</h2>
                          <p className="text-xs text-slate-500">Dynamic network calculations computed directly from database</p>
                        </div>
                        <button
                          onClick={() => setPreviewView('binary_tree')}
                          className="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl shadow"
                        >
                          <GitBranch className="w-3.5 h-3.5 inline mr-1" /> View Binary Tree
                        </button>
                      </div>

                      {/* The 4 Dynamic Team Cards */}
                      <div className="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                        <div className="bg-white p-4 rounded-xl border border-slate-200">
                          <span className="text-slate-500 uppercase text-[10px] font-bold">Direct Team</span>
                          <div className="text-2xl font-bold text-blue-600 my-1">
                            {Object.values(networkMembers).filter((m) => m.sponsorId === 1).length}
                          </div>
                          <span className="text-slate-400">Personal referrals</span>
                        </div>
                        <div className="bg-white p-4 rounded-xl border border-slate-200">
                          <span className="text-slate-500 uppercase text-[10px] font-bold">Binary Team</span>
                          <div className="text-2xl font-bold text-sky-600 my-1">
                            {Object.values(networkMembers).filter((m) => m.id !== 1).length}
                          </div>
                          <span className="text-slate-400">2&times;2 downline matrix</span>
                        </div>
                        <div className="bg-white p-4 rounded-xl border border-slate-200">
                          <span className="text-slate-500 uppercase text-[10px] font-bold">Active Team</span>
                          <div className="text-2xl font-bold text-emerald-600 my-1">
                            {Object.values(networkMembers).filter((m) => m.id !== 1 && m.status === 'active').length}
                          </div>
                          <span className="text-slate-400">Activated accounts</span>
                        </div>
                        <div className="bg-white p-4 rounded-xl border border-slate-200">
                          <span className="text-slate-500 uppercase text-[10px] font-bold">Current Level</span>
                          <div className="text-2xl font-bold text-amber-500 my-1">Level 1</div>
                          <span className="text-slate-400">Joining Package</span>
                        </div>
                      </div>

                      {/* Direct Team Table */}
                      <div className="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                        <div className="px-5 py-3 border-b flex justify-between items-center">
                          <h4 className="font-bold text-slate-900 text-xs">Direct Sponsor Team</h4>
                          <span className="text-[11px] text-slate-400">Sponsored by @ramesh99</span>
                        </div>
                        <table className="w-full text-xs text-left">
                          <thead className="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] border-b">
                            <tr>
                              <th className="px-5 py-2.5">Name</th>
                              <th className="px-4 py-2.5">Username</th>
                              <th className="px-4 py-2.5">Mobile</th>
                              <th className="px-4 py-2.5">Join Date</th>
                              <th className="px-4 py-2.5">Package Level</th>
                              <th className="px-4 py-2.5">Position</th>
                              <th className="px-4 py-2.5">Status</th>
                            </tr>
                          </thead>
                          <tbody className="divide-y divide-slate-100">
                            {Object.values(networkMembers)
                              .filter((m) => m.sponsorId === 1)
                              .map((m) => (
                                <tr key={m.id} className="hover:bg-slate-50">
                                  <td className="px-5 py-2.5 font-bold text-slate-900">{m.fullName}</td>
                                  <td className="px-4 py-2.5 font-mono text-slate-500">@{m.username}</td>
                                  <td className="px-4 py-2.5 font-mono">{m.mobile}</td>
                                  <td className="px-4 py-2.5 text-slate-500">{m.joinDate}</td>
                                  <td className="px-4 py-2.5 font-semibold text-blue-600">Level {m.level}</td>
                                  <td className="px-4 py-2.5">
                                    <span className="bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded uppercase">
                                      {m.position || 'Auto'}
                                    </span>
                                  </td>
                                  <td className="px-4 py-2.5">
                                    <span className="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded">
                                      {m.status}
                                    </span>
                                  </td>
                                </tr>
                              ))}
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    3. MY SPONSOR (Part 4 Core)
                ==================================================== */}
                {previewView === 'sponsor' && (
                  <div className="bg-slate-50 p-6 text-slate-800">
                    <div className="max-w-2xl mx-auto space-y-5">
                      <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                        <div className="text-center mb-6 border-b pb-5">
                          <div className="w-16 h-16 rounded-full bg-blue-100 text-blue-600 font-bold text-2xl flex items-center justify-center mx-auto mb-2">
                            A
                          </div>
                          <h3 className="font-bold text-lg text-slate-900">Administrator</h3>
                          <span className="text-xs font-mono text-slate-500">@admin (Root Sponsor)</span>
                          <div className="mt-2">
                            <span className="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full">
                              Account Active
                            </span>
                          </div>
                        </div>

                        <div className="grid grid-cols-2 gap-3 text-xs">
                          <div className="bg-slate-50 p-3 rounded-xl border">
                            <span className="text-slate-400 block text-[10px]">Sponsor ID</span>
                            <span className="font-mono font-bold text-slate-900">#0 (Root / Company Direct)</span>
                          </div>
                          <div className="bg-slate-50 p-3 rounded-xl border">
                            <span className="text-slate-400 block text-[10px]">Current Level</span>
                            <span className="font-bold text-blue-600">Level 7 (Master)</span>
                          </div>
                          <div className="bg-slate-50 p-3 rounded-xl border">
                            <span className="text-slate-400 block text-[10px]">Relationship Type</span>
                            <span className="font-bold text-slate-900">Direct Sponsor</span>
                          </div>
                          <div className="bg-slate-50 p-3 rounded-xl border">
                            <span className="text-slate-400 block text-[10px]">Binary Tree Status</span>
                            <span className="font-bold text-emerald-600">Root Node</span>
                          </div>
                        </div>

                        <div className="flex justify-between items-center pt-5 mt-5 border-t">
                          <button
                            onClick={() => setPreviewView('team')}
                            className="text-xs text-blue-600 font-bold hover:underline"
                          >
                            &larr; View My Direct Referrals
                          </button>
                          <button
                            onClick={() => setPreviewView('binary_tree')}
                            className="bg-blue-600 text-white font-bold text-xs px-4 py-2 rounded-xl shadow"
                          >
                            Explore Binary Tree &rarr;
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    4. ADMIN TREE MONITORING (Part 4 Core)
                ==================================================== */}
                {previewView === 'admin_tree' && adminTreeData && (
                  <div className="bg-slate-100 p-6 text-slate-900">
                    <div className="max-w-5xl mx-auto space-y-5">
                      <div className="flex flex-wrap justify-between items-center gap-3">
                        <div>
                          <div className="flex items-center gap-2">
                            <span className="bg-rose-600 text-white text-[10px] font-bold px-2 py-0.5 rounded">
                              ADMIN
                            </span>
                            <h2 className="text-xl font-bold">Binary Network Monitoring</h2>
                          </div>
                          <p className="text-xs text-slate-500">
                            Search by username, user ID, or sponsor ID to inspect network trees.
                          </p>
                        </div>

                        {/* Search Bar */}
                        <div className="flex items-center gap-2">
                          <input
                            type="text"
                            value={adminSearchInput}
                            onChange={(e) => setAdminSearchInput(e.target.value)}
                            placeholder="Enter username (e.g. priya22, amit33)..."
                            className="border text-xs px-3 py-1.5 rounded-lg bg-white w-60"
                          />
                          <button
                            onClick={() => {
                              const match = Object.values(networkMembers).find(
                                (m) => m.username.toLowerCase() === adminSearchInput.trim().toLowerCase()
                              );
                              if (match) {
                                setAdminInspectedId(match.id);
                              } else {
                                alert(`No member found matching '${adminSearchInput}'. Try 'priya22' or 'amit33'.`);
                              }
                            }}
                            className="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs px-3.5 py-1.5 rounded-lg shadow-sm"
                          >
                            Inspect
                          </button>
                        </div>
                      </div>

                      {/* Inspected Node Card */}
                      <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm text-xs space-y-3">
                        <div className="flex justify-between items-center border-b pb-3">
                          <div>
                            <span className="text-slate-400 text-[10px] font-bold uppercase">Inspected Node</span>
                            <h4 className="font-bold text-slate-900 text-sm">
                              {adminTreeData.root.fullName} (@{adminTreeData.root.username}) &bull; ID #{adminTreeData.root.id}
                            </h4>
                          </div>
                          <span className="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full">
                            Account Active
                          </span>
                        </div>

                        <div className="grid grid-cols-4 gap-3">
                          <div className="bg-slate-50 p-2.5 rounded-xl border">
                            <span className="text-slate-400 block text-[10px]">Sponsor</span>
                            <span className="font-bold">{adminTreeData.root.sponsorUsername || 'None'}</span>
                          </div>
                          <div className="bg-slate-50 p-2.5 rounded-xl border">
                            <span className="text-slate-400 block text-[10px]">Parent Node</span>
                            <span className="font-bold">{adminTreeData.root.parentId ? `#${adminTreeData.root.parentId}` : 'Root'}</span>
                          </div>
                          <div className="bg-slate-50 p-2.5 rounded-xl border">
                            <span className="text-slate-400 block text-[10px]">Left Child</span>
                            <span className="font-bold text-blue-600">
                              {adminTreeData.left ? `@${adminTreeData.left.username}` : 'Empty'}
                            </span>
                          </div>
                          <div className="bg-slate-50 p-2.5 rounded-xl border">
                            <span className="text-slate-400 block text-[10px]">Right Child</span>
                            <span className="font-bold text-indigo-600">
                              {adminTreeData.right ? `@${adminTreeData.right.username}` : 'Empty'}
                            </span>
                          </div>
                        </div>
                      </div>

                      {/* Admin Visual Tree */}
                      <div className="bg-white rounded-2xl border border-slate-200 p-6 overflow-x-auto text-center shadow-sm">
                        <div className="flex justify-center mb-4">
                          <div className="bg-white border-2 border-blue-600 p-2.5 rounded-xl w-40 text-center shadow-xs">
                            <div className="font-bold text-xs">{adminTreeData.root.fullName}</div>
                            <span className="text-[10px] text-slate-500 font-mono">@{adminTreeData.root.username}</span>
                          </div>
                        </div>

                        <div className="flex justify-around">
                          <div className="w-1/2 flex justify-center">
                            {adminTreeData.left ? (
                              <div
                                onClick={() => setAdminInspectedId(adminTreeData.left!.id)}
                                className="bg-white border-2 border-sky-400 p-2.5 rounded-xl w-40 text-center shadow-xs cursor-pointer hover:shadow-md transition"
                              >
                                <span className="bg-blue-100 text-blue-800 text-[9px] font-bold px-1.5 py-0.5 rounded uppercase">Left</span>
                                <div className="font-bold text-xs mt-1">{adminTreeData.left.fullName}</div>
                                <span className="text-[10px] text-slate-500 font-mono">@{adminTreeData.left.username}</span>
                              </div>
                            ) : (
                              <div className="border border-dashed border-slate-300 p-3 rounded-xl w-40 text-slate-400 text-xs">
                                Empty Left Slot
                              </div>
                            )}
                          </div>

                          <div className="w-1/2 flex justify-center">
                            {adminTreeData.right ? (
                              <div
                                onClick={() => setAdminInspectedId(adminTreeData.right!.id)}
                                className="bg-white border-2 border-indigo-400 p-2.5 rounded-xl w-40 text-center shadow-xs cursor-pointer hover:shadow-md transition"
                              >
                                <span className="bg-indigo-100 text-indigo-800 text-[9px] font-bold px-1.5 py-0.5 rounded uppercase">Right</span>
                                <div className="font-bold text-xs mt-1">{adminTreeData.right.fullName}</div>
                                <span className="text-[10px] text-slate-500 font-mono">@{adminTreeData.right.username}</span>
                              </div>
                            ) : (
                              <div className="border border-dashed border-slate-300 p-3 rounded-xl w-40 text-slate-400 text-xs">
                                Empty Right Slot
                              </div>
                            )}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    5. OTHER PREVIEWS (Packages, Dash, Register)
                ==================================================== */}
                {previewView === 'user_dash' && (
                  <div className="bg-slate-50 p-6 text-slate-800">
                    <div className="max-w-4xl mx-auto space-y-4 text-xs">
                      <div className="bg-slate-900 text-white p-5 rounded-2xl flex justify-between items-center">
                        <div>
                          <h3 className="font-bold text-base">Welcome, Ramesh Kumar!</h3>
                          <p className="text-slate-400 text-xs">Direct Referrals: 3 &bull; Binary Downline: 4</p>
                        </div>
                        <button
                          onClick={() => setPreviewView('binary_tree')}
                          className="bg-sky-500 text-slate-950 font-bold px-3 py-1.5 rounded-lg text-xs"
                        >
                          Binary Tree &rarr;
                        </button>
                      </div>
                    </div>
                  </div>
                )}

                {previewView === 'register' && (
                  <div className="bg-slate-100 p-8 min-h-[460px] flex items-center justify-center">
                    <div className="bg-white p-6 rounded-2xl shadow border max-w-md w-full text-xs space-y-3">
                      <h3 className="font-bold text-base text-slate-900 text-center">Part 4 Registration</h3>
                      <p className="text-slate-500 text-center text-[11px]">Sponsor Validation &amp; 2&times;2 Binary Placement</p>

                      <div>
                        <label className="font-semibold block mb-1">Sponsor Username / ID *</label>
                        <input type="text" defaultValue="ramesh99" className="w-full border p-2 rounded-lg bg-slate-50" />
                        <span className="text-[10px] text-emerald-600 font-medium">Valid active sponsor verified</span>
                      </div>

                      <div className="bg-slate-50 p-3 rounded-xl border space-y-2">
                        <span className="font-bold text-[11px] block">Binary Placement (Optional)</span>
                        <div className="grid grid-cols-2 gap-2">
                          <input type="text" placeholder="Parent Username" className="border p-1.5 rounded text-[11px]" />
                          <select className="border p-1.5 rounded text-[11px]">
                            <option>Auto (First Open Slot)</option>
                            <option>Left Leg</option>
                            <option>Right Leg</option>
                          </select>
                        </div>
                      </div>

                      <button
                        onClick={() => setPreviewView('binary_tree')}
                        className="w-full bg-blue-600 text-white font-bold py-2 rounded-lg shadow mt-2"
                      >
                        Register Member &rarr;
                      </button>
                    </div>
                  </div>
                )}

                {previewView === 'packages' && (
                  <div className="bg-slate-50 p-6 text-slate-800 text-xs">
                    <div className="max-w-4xl mx-auto space-y-3">
                      <h3 className="font-bold text-base text-slate-900">Official SS Dream Packages</h3>
                      <div className="grid grid-cols-3 gap-3">
                        {PLAN_PACKAGES.slice(0, 3).map((p) => (
                          <div key={p.level} className="bg-white p-4 rounded-xl border shadow-xs">
                            <span className="font-bold text-blue-600">{p.name}</span>
                            <div className="text-xl font-bold my-1">₹{p.price.toLocaleString('en-IN')}</div>
                            <span className="text-slate-500">Team Req: {p.teamSize} Members</span>
                          </div>
                        ))}
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    UPGRADE HISTORY (user/upgrade-history.php)
                ==================================================== */}
                {previewView === 'upgrade_history' && (
                  <div className="bg-slate-100 p-6 text-slate-900 text-xs">
                    <div className="max-w-5xl mx-auto space-y-4">
                      {/* Top Banner */}
                      <div className="bg-gradient-to-r from-[#0b192c] to-[#1e3e62] text-white p-5 rounded-2xl flex flex-wrap justify-between items-center gap-3 shadow-sm">
                        <div>
                          <div className="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span className="bg-blue-500/20 text-blue-300 border border-blue-400/30 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                              Member Portal
                            </span>
                            <span className="bg-amber-400 text-slate-950 text-[10px] font-bold px-2.5 py-0.5 rounded-full">
                              Active: Level 2 (₹2,000)
                            </span>
                          </div>
                          <h3 className="font-bold text-base text-white">Package Upgrade Requests Ledger</h3>
                          <p className="text-slate-300 text-[11px]">
                            Track all membership level upgrades, review statuses, references, and administrative remarks.
                          </p>
                        </div>
                        <button
                          onClick={() => setPreviewView('packages')}
                          className="bg-sky-500 hover:bg-sky-400 text-slate-950 font-bold px-4 py-2 rounded-xl text-xs transition shadow"
                        >
                          Packages &amp; Upgrade &rarr;
                        </button>
                      </div>

                      {/* Summary Cards */}
                      <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <button
                          onClick={() => setHistoryFilter('all')}
                          className={`p-3.5 bg-white rounded-xl border text-left transition ${
                            historyFilter === 'all' ? 'border-sky-500 ring-2 ring-sky-500/20' : 'border-slate-200'
                          }`}
                        >
                          <span className="text-slate-400 text-[10px] uppercase font-bold block">Total Requests</span>
                          <span className="text-lg font-bold text-slate-900">3</span>
                          <span className="text-[10px] text-slate-400 block mt-0.5">All applications</span>
                        </button>

                        <button
                          onClick={() => setHistoryFilter('approved')}
                          className={`p-3.5 bg-white rounded-xl border text-left transition ${
                            historyFilter === 'approved' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200'
                          }`}
                        >
                          <span className="text-slate-400 text-[10px] uppercase font-bold block">Approved</span>
                          <span className="text-lg font-bold text-emerald-600">1</span>
                          <span className="text-[10px] text-emerald-600 block mt-0.5">Activated upgrade</span>
                        </button>

                        <button
                          onClick={() => setHistoryFilter('pending')}
                          className={`p-3.5 bg-white rounded-xl border text-left transition ${
                            historyFilter === 'pending' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200'
                          }`}
                        >
                          <span className="text-slate-400 text-[10px] uppercase font-bold block">Pending Review</span>
                          <span className="text-lg font-bold text-amber-500">1</span>
                          <span className="text-[10px] text-amber-500 block mt-0.5">Awaiting admin</span>
                        </button>

                        <button
                          onClick={() => setHistoryFilter('rejected')}
                          className={`p-3.5 bg-white rounded-xl border text-left transition ${
                            historyFilter === 'rejected' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-200'
                          }`}
                        >
                          <span className="text-slate-400 text-[10px] uppercase font-bold block">Rejected</span>
                          <span className="text-lg font-bold text-rose-600">1</span>
                          <span className="text-[10px] text-rose-500 block mt-0.5">Declined with note</span>
                        </button>
                      </div>

                      {/* Paginated Table Card */}
                      <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div className="p-4 border-b border-slate-200 flex flex-wrap justify-between items-center gap-2">
                          <div>
                            <h4 className="font-bold text-slate-900 text-sm">Upgrade Applications</h4>
                            <p className="text-[11px] text-slate-400">user/upgrade-history.php live interface</p>
                          </div>

                          {/* Filter Pills */}
                          <div className="flex items-center gap-1 bg-slate-100 p-1 rounded-lg">
                            {(['all', 'pending', 'approved', 'rejected'] as const).map((filterKey) => (
                              <button
                                key={filterKey}
                                onClick={() => setHistoryFilter(filterKey)}
                                className={`px-2.5 py-1 rounded font-semibold text-[11px] capitalize transition ${
                                  historyFilter === filterKey
                                    ? 'bg-white text-slate-900 shadow-xs'
                                    : 'text-slate-500 hover:text-slate-900'
                                }`}
                              >
                                {filterKey}
                              </button>
                            ))}
                          </div>
                        </div>

                        <div className="overflow-x-auto">
                          <table className="w-full text-left border-collapse">
                            <thead>
                              <tr className="bg-slate-50 text-slate-500 text-[11px] border-b">
                                <th className="p-3 pl-4">Request ID</th>
                                <th className="p-3">Level Progression</th>
                                <th className="p-3">Package &amp; Amount</th>
                                <th className="p-3">Payment Method</th>
                                <th className="p-3">Submitted Date</th>
                                <th className="p-3">Status</th>
                                <th className="p-3">Admin Remarks</th>
                                <th className="p-3 pr-4 text-center">Action</th>
                              </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 text-xs">
                              {[
                                {
                                  id: 104,
                                  fromLevel: 2,
                                  toLevel: 3,
                                  packageName: 'Level 3 Package',
                                  amount: 3000,
                                  paymentMode: 'Wallet Balance',
                                  transactionRef: 'WAL-TX-884920',
                                  date: '14 Oct 2026, 04:30 PM',
                                  reviewedAt: 'Pending review',
                                  status: 'pending' as const,
                                  remarks: 'Request currently queued for administrator approval.',
                                },
                                {
                                  id: 89,
                                  fromLevel: 1,
                                  toLevel: 2,
                                  packageName: 'Level 2 Package',
                                  amount: 2000,
                                  paymentMode: 'Direct UPI / Bank Transfer',
                                  transactionRef: 'UPI-REF-99281741',
                                  date: '10 Oct 2026, 11:15 AM',
                                  reviewedAt: '10 Oct 2026, 01:20 PM',
                                  status: 'approved' as const,
                                  remarks: 'Verified bank transaction reference UPI-REF-99281741. Member advanced to Level 2.',
                                },
                                {
                                  id: 42,
                                  fromLevel: 1,
                                  toLevel: 2,
                                  packageName: 'Level 2 Package',
                                  amount: 2000,
                                  paymentMode: 'Cash / Admin Approval',
                                  transactionRef: 'N/A',
                                  date: '05 Oct 2026, 09:00 AM',
                                  reviewedAt: '05 Oct 2026, 10:30 AM',
                                  status: 'rejected' as const,
                                  remarks: 'Incomplete transaction proof. Please submit with valid UTR number.',
                                },
                              ]
                                .filter((item) => historyFilter === 'all' || item.status === historyFilter)
                                .map((item) => (
                                  <tr key={item.id} className="hover:bg-slate-50/80 transition">
                                    <td className="p-3 pl-4 font-mono font-bold text-slate-800">
                                      #UPG-{String(item.id).padStart(5, '0')}
                                    </td>
                                    <td className="p-3">
                                      <div className="flex items-center gap-1.5">
                                        <span className="bg-slate-100 border text-slate-600 px-1.5 py-0.5 rounded text-[10px] font-semibold">
                                          L{item.fromLevel}
                                        </span>
                                        <ArrowRight className="w-3 h-3 text-slate-400" />
                                        <span className="bg-sky-100 text-sky-800 border border-sky-200 px-1.5 py-0.5 rounded text-[10px] font-bold">
                                          L{item.toLevel}
                                        </span>
                                      </div>
                                    </td>
                                    <td className="p-3">
                                      <div className="font-bold text-slate-900">{item.packageName}</div>
                                      <div className="text-emerald-600 font-mono font-semibold">
                                        ₹{item.amount.toLocaleString('en-IN')}
                                      </div>
                                    </td>
                                    <td className="p-3 text-slate-600">
                                      <div>{item.paymentMode}</div>
                                      <div className="text-[10px] text-slate-400 font-mono">Ref: {item.transactionRef}</div>
                                    </td>
                                    <td className="p-3 text-slate-600 text-[11px]">{item.date}</td>
                                    <td className="p-3">
                                      {item.status === 'pending' && (
                                        <span className="inline-flex items-center gap-1 bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                          <Clock className="w-3 h-3" /> Pending Review
                                        </span>
                                      )}
                                      {item.status === 'approved' && (
                                        <span className="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                          <CheckCircle className="w-3 h-3" /> Approved
                                        </span>
                                      )}
                                      {item.status === 'rejected' && (
                                        <span className="inline-flex items-center gap-1 bg-rose-100 text-rose-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                          <AlertCircle className="w-3 h-3" /> Rejected
                                        </span>
                                      )}
                                    </td>
                                    <td className="p-3 text-slate-500 text-[11px] max-w-xs truncate">
                                      {item.remarks}
                                    </td>
                                    <td className="p-3 pr-4 text-center">
                                      <button
                                        onClick={() => setHistoryModalItem(item)}
                                        className="border border-sky-500 text-sky-600 hover:bg-sky-50 font-semibold px-2.5 py-1 rounded-full text-[11px] transition inline-flex items-center gap-1"
                                      >
                                        <Eye className="w-3 h-3" /> Details
                                      </button>
                                    </td>
                                  </tr>
                                ))}
                            </tbody>
                          </table>
                        </div>

                        {/* Pagination Footer */}
                        <div className="p-3 bg-slate-50 border-t border-slate-200 flex flex-wrap justify-between items-center text-[11px] text-slate-500">
                          <span>Showing <strong>1 to 3</strong> of <strong>3</strong> requests</span>
                          <div className="flex items-center gap-1">
                            <button className="px-2.5 py-1 border rounded bg-white text-slate-400 cursor-not-allowed">
                              Previous
                            </button>
                            <button className="px-2.5 py-1 border rounded bg-sky-600 text-white font-bold">1</button>
                            <button className="px-2.5 py-1 border rounded bg-white text-slate-400 cursor-not-allowed">
                              Next
                            </button>
                          </div>
                        </div>
                      </div>

                      {/* Modal Simulation */}
                      {historyModalItem && (
                        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
                          <div className="bg-white rounded-2xl max-w-md w-full shadow-2xl border overflow-hidden">
                            <div className="bg-slate-900 text-white p-4 flex justify-between items-center">
                              <h5 className="font-bold text-sm flex items-center gap-2">
                                <History className="w-4 h-4 text-sky-400" />
                                Request #UPG-{String(historyModalItem.id).padStart(5, '0')}
                              </h5>
                              <button
                                onClick={() => setHistoryModalItem(null)}
                                className="text-slate-400 hover:text-white"
                              >
                                <X className="w-4 h-4" />
                              </button>
                            </div>

                            <div className="p-5 space-y-3.5 text-xs">
                              <div className="flex justify-between items-center border-b pb-3">
                                <div>
                                  <span className="text-slate-400 text-[10px] block">Status</span>
                                  <span className="font-bold capitalize text-slate-900">{historyModalItem.status}</span>
                                </div>
                                <div className="text-end">
                                  <span className="text-slate-400 text-[10px] block">Package Amount</span>
                                  <span className="text-base font-bold text-emerald-600 font-mono">
                                    ₹{historyModalItem.amount.toLocaleString('en-IN')}
                                  </span>
                                </div>
                              </div>

                              <div className="grid grid-cols-2 gap-3 text-[11px]">
                                <div>
                                  <span className="text-slate-400 block text-[10px]">Level Progression</span>
                                  <span className="font-bold">Level {historyModalItem.fromLevel} &rarr; Level {historyModalItem.toLevel}</span>
                                </div>
                                <div>
                                  <span className="text-slate-400 block text-[10px]">Payment Mode</span>
                                  <span className="font-semibold">{historyModalItem.paymentMode}</span>
                                </div>
                                <div>
                                  <span className="text-slate-400 block text-[10px]">Transaction Reference</span>
                                  <span className="font-mono">{historyModalItem.transactionRef}</span>
                                </div>
                                <div>
                                  <span className="text-slate-400 block text-[10px]">Submitted Date</span>
                                  <span>{historyModalItem.date}</span>
                                </div>
                              </div>

                              <div className="bg-slate-50 p-3 rounded-xl border space-y-1">
                                <span className="text-slate-500 font-bold text-[10px] block">Administrator Remarks</span>
                                <p className="text-slate-700 text-xs italic">{historyModalItem.remarks}</p>
                              </div>

                              <div className="flex justify-end gap-2 pt-2">
                                <button
                                  onClick={() => setHistoryModalItem(null)}
                                  className="px-4 py-1.5 rounded-lg border text-slate-600 hover:bg-slate-100"
                                >
                                  Close
                                </button>
                                <button
                                  onClick={() => {
                                    setHistoryModalItem(null);
                                    setPreviewView('packages');
                                  }}
                                  className="px-4 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white font-bold"
                                >
                                  View Packages &rarr;
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      )}
                    </div>
                  </div>
                )}

                {/* ====================================================
                    VIEW: USER INCOME CENTER (user/income.php)
                ==================================================== */}
                {previewView === 'income_user' && (
                  <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden text-slate-900">
                    {/* Header */}
                    <div className="bg-gradient-to-r from-emerald-700 via-teal-700 to-slate-900 p-6 text-white">
                      <div className="flex flex-wrap items-center justify-between gap-4">
                        <div>
                          <div className="flex items-center gap-2 mb-1">
                            <span className="bg-emerald-500/20 text-emerald-200 text-[10px] font-bold px-2 py-0.5 rounded-full border border-emerald-400/30">
                              Part 5 Official Income Engine
                            </span>
                            <span className="bg-sky-500/20 text-sky-200 text-[10px] font-bold px-2 py-0.5 rounded-full">
                              Member: rohit99 (Level 2 Active)
                            </span>
                          </div>
                          <h3 className="text-xl font-bold flex items-center gap-2">
                            <TrendingUp className="w-5 h-5 text-emerald-300" /> Income Center &amp; Earnings Ledger
                          </h3>
                          <p className="text-xs text-emerald-100/80 mt-1">
                            Real-time earnings ledger across the 3 locked SS Dream income streams. All approved earnings are automatically credited to your wallet balance.
                          </p>
                        </div>

                        <div className="bg-white/10 backdrop-blur-md px-4 py-3 rounded-xl border border-white/20 text-right">
                          <span className="text-[11px] text-emerald-200 block">Total Lifetime Earnings</span>
                          <span className="text-2xl font-black font-mono text-white">₹6,200.00</span>
                          <span className="text-[10px] text-emerald-300 flex items-center justify-end gap-1 mt-0.5">
                            <CheckCircle className="w-3 h-3" /> Auto-Credited to Wallet
                          </span>
                        </div>
                      </div>
                    </div>

                    <div className="p-6 space-y-6">
                      {/* Locked Business Rules Banner */}
                      <div className="bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <div className="flex items-start gap-3">
                          <AlertTriangle className="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                          <div className="text-xs text-amber-900 space-y-1">
                            <div className="font-bold text-amber-950 flex items-center gap-2">
                              <span>3 Official Income Rules (Strictly Locked - No Deviation):</span>
                            </div>
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-2 pt-1 font-mono text-[11px]">
                              <div className="bg-white p-2 rounded-lg border border-amber-200/80">
                                <span className="font-bold text-emerald-700 block">1. Direct Income: 100%</span>
                                <span className="text-[10px] text-slate-600 font-sans">On Level 1 referral entry (₹1,200)</span>
                              </div>
                              <div className="bg-white p-2 rounded-lg border border-amber-200/80">
                                <span className="font-bold text-sky-700 block">2. Level Income: 50%</span>
                                <span className="text-[10px] text-slate-600 font-sans">Level 2 &rarr; Level 7 (User must be upgraded!)</span>
                              </div>
                              <div className="bg-white p-2 rounded-lg border border-amber-200/80">
                                <span className="font-bold text-purple-700 block">3. Sponsor Income: 50%</span>
                                <span className="text-[10px] text-slate-600 font-sans">Level 2 &rarr; Unlimited Level</span>
                              </div>
                            </div>
                            <div className="text-[11px] text-amber-800 pt-1 font-sans">
                              <strong>Crucial Business Validation:</strong> Users only receive Level Income for a level if they have already upgraded to that level or higher.
                            </div>
                          </div>
                        </div>
                      </div>

                      {/* 4 Metric Cards */}
                      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div className="bg-slate-50 p-4 rounded-xl border border-slate-200">
                          <div className="flex items-center justify-between text-slate-500 text-xs mb-1">
                            <span>Total Earnings</span>
                            <Wallet className="w-4 h-4 text-emerald-600" />
                          </div>
                          <div className="text-2xl font-bold font-mono text-slate-900">₹6,200</div>
                          <div className="text-[10px] text-emerald-600 font-medium mt-1 flex items-center gap-1">
                            <CheckCircle className="w-3 h-3" /> Credited to Wallet
                          </div>
                        </div>

                        <div className="bg-emerald-50/50 p-4 rounded-xl border border-emerald-200">
                          <div className="flex items-center justify-between text-emerald-700 text-xs mb-1">
                            <span>Direct Income (100%)</span>
                            <Award className="w-4 h-4 text-emerald-600" />
                          </div>
                          <div className="text-2xl font-bold font-mono text-emerald-700">₹2,400</div>
                          <div className="text-[10px] text-slate-500 mt-1">2 Direct Referrals (₹1,200 each)</div>
                        </div>

                        <div className="bg-sky-50/50 p-4 rounded-xl border border-sky-200">
                          <div className="flex items-center justify-between text-sky-700 text-xs mb-1">
                            <span>Level Income (50%)</span>
                            <Layers className="w-4 h-4 text-sky-600" />
                          </div>
                          <div className="text-2xl font-bold font-mono text-sky-700">₹1,000</div>
                          <div className="text-[10px] text-slate-500 mt-1">Level 2: 1 downline upgraded (₹2,000 &times; 50%)</div>
                        </div>

                        <div className="bg-purple-50/50 p-4 rounded-xl border border-purple-200">
                          <div className="flex items-center justify-between text-purple-700 text-xs mb-1">
                            <span>Sponsor Income (50%)</span>
                            <Users className="w-4 h-4 text-purple-600" />
                          </div>
                          <div className="text-2xl font-bold font-mono text-purple-700">₹2,800</div>
                          <div className="text-[10px] text-slate-500 mt-1">Team upgrades (₹1,000 + ₹1,500 + etc)</div>
                        </div>
                      </div>

                      {/* Interactive Simulation Panel for Upgrade Eligibility */}
                      <div className="bg-gradient-to-r from-slate-900 to-slate-800 text-white p-5 rounded-2xl border border-slate-700 space-y-3">
                        <div className="flex flex-wrap items-center justify-between gap-3">
                          <div>
                            <span className="text-[10px] font-bold uppercase tracking-wider text-amber-400 block">
                              Interactive Rule Verification Simulator
                            </span>
                            <h4 className="font-bold text-sm text-white flex items-center gap-2">
                              Test Level Upgrade Eligibility &amp; Income Distribution
                            </h4>
                          </div>
                          <span className="text-xs bg-slate-800 text-slate-300 px-2.5 py-1 rounded-lg border border-slate-700 font-mono">
                            Current User: rohit99 (Level 2)
                          </span>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs pt-1">
                          <button
                            onClick={() => {
                              setTestSimulateResult(
                                'SUCCESS: Downline member sunita_k upgraded to Level 2 (₹2,000). rohit99 is currently at Level 2, which satisfies the condition (Level 2 >= Level 2). ₹1,000 (50%) Level Income credited to rohit99! Sponsor admin received ₹1,000 (50%) Sponsor Income.'
                              );
                            }}
                            className="p-3 rounded-xl bg-slate-800/90 hover:bg-slate-700/90 border border-emerald-500/30 text-left transition space-y-1"
                          >
                            <div className="font-bold text-emerald-400 flex items-center gap-1.5">
                              <CheckCircle className="w-4 h-4" /> Case A: Downline Upgrades to Level 2 (₹2,000)
                            </div>
                            <p className="text-slate-300 text-[11px]">
                              rohit99 is on Level 2 &rarr; <strong>ELIGIBLE</strong> for Level 2 Income (₹1,000 credited).
                            </p>
                          </button>

                          <button
                            onClick={() => {
                              setTestSimulateResult(
                                'RULE ENFORCED: Downline member deepak_v upgraded to Level 3 (₹3,000). rohit99 is currently only at Level 2 (Level 2 < Level 3). Income calculation engine SKIPPED Level Income for rohit99 because member must upgrade to Level 3 first!'
                              );
                            }}
                            className="p-3 rounded-xl bg-slate-800/90 hover:bg-slate-700/90 border border-amber-500/30 text-left transition space-y-1"
                          >
                            <div className="font-bold text-amber-400 flex items-center gap-1.5">
                              <AlertCircle className="w-4 h-4" /> Case B: Downline Upgrades to Level 3 (₹3,000)
                            </div>
                            <p className="text-slate-300 text-[11px]">
                              rohit99 is on Level 2 &rarr; <strong>INELIGIBLE</strong> for Level 3 Income until rohit99 upgrades to Level 3!
                            </p>
                          </button>
                        </div>

                        {testSimulateResult && (
                          <div className="p-3 rounded-xl bg-slate-950/80 border border-slate-700 text-xs text-slate-200 flex items-start justify-between gap-3 font-mono">
                            <div>{testSimulateResult}</div>
                            <button
                              onClick={() => setTestSimulateResult(null)}
                              className="text-slate-400 hover:text-white shrink-0"
                            >
                              <X className="w-4 h-4" />
                            </button>
                          </div>
                        )}
                      </div>

                      {/* Income Ledger Table */}
                      <div className="space-y-3">
                        <div className="flex flex-wrap items-center justify-between gap-3">
                          <h4 className="font-bold text-sm text-slate-900 flex items-center gap-2">
                            <FileText className="w-4 h-4 text-emerald-600" /> Earnings History &amp; Breakdown
                          </h4>

                          {/* Filter Tabs */}
                          <div className="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs">
                            {(
                              [
                                { id: 'all', label: 'All Streams' },
                                { id: 'direct', label: 'Direct (100%)' },
                                { id: 'level', label: 'Level (50%)' },
                                { id: 'sponsor', label: 'Sponsor (50%)' },
                              ] as const
                            ).map((tab) => (
                              <button
                                key={tab.id}
                                onClick={() => setIncomeTypeFilter(tab.id)}
                                className={`px-3 py-1 rounded-lg font-medium transition ${
                                  incomeTypeFilter === tab.id
                                    ? 'bg-white text-slate-900 shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900'
                                }`}
                              >
                                {tab.label}
                              </button>
                            ))}
                          </div>
                        </div>

                        <div className="overflow-x-auto rounded-xl border border-slate-200">
                          <table className="w-full text-left border-collapse text-xs">
                            <thead>
                              <tr className="bg-slate-50 text-slate-500 text-[11px] border-b">
                                <th className="p-3 pl-4">Income Ref</th>
                                <th className="p-3">Stream Type</th>
                                <th className="p-3">Source Member</th>
                                <th className="p-3">Base Package</th>
                                <th className="p-3">Rate</th>
                                <th className="p-3">Amount Credited</th>
                                <th className="p-3">Date &amp; Time</th>
                                <th className="p-3 pr-4">Wallet Ledger</th>
                              </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                              {[
                                {
                                  id: 'INC-DIR-00018',
                                  type: 'direct',
                                  typeName: 'Direct Income',
                                  typeBadge: 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                  fromUser: 'vikram_singh',
                                  fromName: 'Vikram Singh',
                                  fromLevel: 1,
                                  packageAmount: 1200,
                                  percentage: '100%',
                                  amount: 1200,
                                  date: '14 Oct 2026, 02:45 PM',
                                  txnRef: 'TXN-DIR-88192',
                                },
                                {
                                  id: 'INC-LVL-00042',
                                  type: 'level',
                                  typeName: 'Level Income',
                                  typeBadge: 'bg-sky-100 text-sky-800 border-sky-200',
                                  fromUser: 'sunita_k',
                                  fromName: 'Sunita Kumar',
                                  fromLevel: 2,
                                  packageAmount: 2000,
                                  percentage: '50%',
                                  amount: 1000,
                                  date: '12 Oct 2026, 06:10 PM',
                                  txnRef: 'TXN-LVL-99412',
                                },
                                {
                                  id: 'INC-SPO-00055',
                                  type: 'sponsor',
                                  typeName: 'Sponsor Income',
                                  typeBadge: 'bg-purple-100 text-purple-800 border-purple-200',
                                  fromUser: 'priya_sharma',
                                  fromName: 'Priya Sharma',
                                  fromLevel: 2,
                                  packageAmount: 2000,
                                  percentage: '50%',
                                  amount: 1000,
                                  date: '11 Oct 2026, 11:30 AM',
                                  txnRef: 'TXN-SPO-77123',
                                },
                                {
                                  id: 'INC-SPO-00062',
                                  type: 'sponsor',
                                  typeName: 'Sponsor Income',
                                  typeBadge: 'bg-purple-100 text-purple-800 border-purple-200',
                                  fromUser: 'amit_p',
                                  fromName: 'Amit Patel',
                                  fromLevel: 3,
                                  packageAmount: 3000,
                                  percentage: '50%',
                                  amount: 1500,
                                  date: '09 Oct 2026, 04:15 PM',
                                  txnRef: 'TXN-SPO-66231',
                                },
                                {
                                  id: 'INC-DIR-00004',
                                  type: 'direct',
                                  typeName: 'Direct Income',
                                  typeBadge: 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                  fromUser: 'priya_sharma',
                                  fromName: 'Priya Sharma',
                                  fromLevel: 1,
                                  packageAmount: 1200,
                                  percentage: '100%',
                                  amount: 1200,
                                  date: '02 Oct 2026, 10:00 AM',
                                  txnRef: 'TXN-DIR-11029',
                                },
                              ]
                                .filter((item) => incomeTypeFilter === 'all' || item.type === incomeTypeFilter)
                                .map((item) => (
                                  <tr key={item.id} className="hover:bg-slate-50 transition">
                                    <td className="p-3 pl-4 font-mono font-bold text-slate-800">{item.id}</td>
                                    <td className="p-3">
                                      <span
                                        className={`inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full border ${item.typeBadge}`}
                                      >
                                        {item.typeName} ({item.percentage})
                                      </span>
                                    </td>
                                    <td className="p-3">
                                      <div className="font-semibold text-slate-900">{item.fromName}</div>
                                      <div className="text-[10px] text-slate-500 font-mono">
                                        @{item.fromUser} &bull; L{item.fromLevel}
                                      </div>
                                    </td>
                                    <td className="p-3 font-mono text-slate-600">
                                      ₹{item.packageAmount.toLocaleString('en-IN')}
                                    </td>
                                    <td className="p-3 font-bold text-slate-700">{item.percentage}</td>
                                    <td className="p-3">
                                      <span className="font-mono font-bold text-emerald-600 text-sm">
                                        +₹{item.amount.toLocaleString('en-IN')}
                                      </span>
                                    </td>
                                    <td className="p-3 text-[11px] text-slate-500">{item.date}</td>
                                    <td className="p-3 pr-4">
                                      <span className="font-mono text-[10px] text-slate-400 block">{item.txnRef}</span>
                                      <span className="text-[10px] font-semibold text-emerald-600 flex items-center gap-0.5">
                                        <Check className="w-3 h-3" /> Credited
                                      </span>
                                    </td>
                                  </tr>
                                ))}
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {/* ====================================================
                    VIEW: ADMIN INCOME AUDIT (admin/incomes.php)
                ==================================================== */}
                {previewView === 'income_admin' && (
                  <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden text-slate-900">
                    {/* Header */}
                    <div className="bg-gradient-to-r from-amber-700 via-amber-800 to-slate-950 p-6 text-white">
                      <div className="flex flex-wrap items-center justify-between gap-4">
                        <div>
                          <div className="flex items-center gap-2 mb-1">
                            <span className="bg-amber-500/20 text-amber-200 text-[10px] font-bold px-2 py-0.5 rounded-full border border-amber-400/30">
                              System Master Income Oversight
                            </span>
                            <span className="bg-emerald-500/20 text-emerald-300 text-[10px] font-bold px-2 py-0.5 rounded-full">
                              Engine Status: Online &amp; Idempotent
                            </span>
                          </div>
                          <h3 className="text-xl font-bold flex items-center gap-2">
                            <DollarSign className="w-5 h-5 text-amber-300" /> Admin Income Audit &amp; Distribution Ledger
                          </h3>
                          <p className="text-xs text-amber-100/80 mt-1">
                            Live system audit of all distributed earnings across members. Enforces strict zero-duplicate distribution and PDO transaction isolation.
                          </p>
                        </div>

                        <div className="bg-white/10 backdrop-blur-md px-4 py-3 rounded-xl border border-white/20 text-right">
                          <span className="text-[11px] text-amber-200 block">Total System Incomes Distributed</span>
                          <span className="text-2xl font-black font-mono text-white">₹3,92,400.00</span>
                          <span className="text-[10px] text-emerald-300 flex items-center justify-end gap-1 mt-0.5">
                            <CheckCircle className="w-3 h-3" /> 100% Math Verification Passed
                          </span>
                        </div>
                      </div>
                    </div>

                    <div className="p-6 space-y-6">
                      {/* Metric Cards */}
                      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div className="bg-slate-50 p-4 rounded-xl border border-slate-200">
                          <div className="text-slate-500 text-xs mb-1">Total System Volume</div>
                          <div className="text-2xl font-bold font-mono text-slate-900">₹3,92,400</div>
                          <div className="text-[10px] text-slate-500 mt-1">148 total distributed payouts</div>
                        </div>

                        <div className="bg-emerald-50/50 p-4 rounded-xl border border-emerald-200">
                          <div className="text-emerald-700 text-xs mb-1">Direct Incomes (100%)</div>
                          <div className="text-2xl font-bold font-mono text-emerald-700">₹74,400</div>
                          <div className="text-[10px] text-slate-500 mt-1">62 activations &times; ₹1,200</div>
                        </div>

                        <div className="bg-sky-50/50 p-4 rounded-xl border border-sky-200">
                          <div className="text-sky-700 text-xs mb-1">Level Incomes (50%)</div>
                          <div className="text-2xl font-bold font-mono text-sky-700">₹1,59,000</div>
                          <div className="text-[10px] text-slate-500 mt-1">43 verified level distributions</div>
                        </div>

                        <div className="bg-purple-50/50 p-4 rounded-xl border border-purple-200">
                          <div className="text-purple-700 text-xs mb-1">Sponsor Incomes (50%)</div>
                          <div className="text-2xl font-bold font-mono text-purple-700">₹1,59,000</div>
                          <div className="text-[10px] text-slate-500 mt-1">43 verified sponsor distributions</div>
                        </div>
                      </div>

                      {/* Official Package Commission Reference */}
                      <div className="bg-slate-50 p-5 rounded-xl border border-slate-200 space-y-3">
                        <h4 className="font-bold text-xs uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                          <ShieldCheck className="w-4 h-4 text-emerald-600" /> Locked Business Document Commission Matrix
                        </h4>

                        <div className="overflow-x-auto">
                          <table className="w-full text-left border-collapse text-xs">
                            <thead>
                              <tr className="bg-white border-b text-[11px] text-slate-500">
                                <th className="p-2.5 pl-3">Level</th>
                                <th className="p-2.5">Package Price</th>
                                <th className="p-2.5">2&times;2 Team</th>
                                <th className="p-2.5">Direct Income</th>
                                <th className="p-2.5">Level Income (50%)</th>
                                <th className="p-2.5">Sponsor Income (50%)</th>
                                <th className="p-2.5 pr-3">Max Level Potential</th>
                              </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 font-mono">
                              {PLAN_PACKAGES.map((pkg) => (
                                <tr key={pkg.level} className="hover:bg-white transition">
                                  <td className="p-2.5 pl-3 font-bold text-slate-800">Level {pkg.level}</td>
                                  <td className="p-2.5 font-bold text-slate-900">₹{pkg.price.toLocaleString('en-IN')}</td>
                                  <td className="p-2.5 text-slate-600 font-sans">{pkg.teamSize} members</td>
                                  <td className="p-2.5">
                                    {pkg.level === 1 ? (
                                      <span className="font-bold text-emerald-600">100% (₹1,200)</span>
                                    ) : (
                                      <span className="text-slate-400">-</span>
                                    )}
                                  </td>
                                  <td className="p-2.5">
                                    {pkg.level >= 2 ? (
                                      <span className="font-bold text-sky-600">50% (₹{(pkg.price * 0.5).toLocaleString('en-IN')})</span>
                                    ) : (
                                      <span className="text-slate-400">-</span>
                                    )}
                                  </td>
                                  <td className="p-2.5">
                                    {pkg.level >= 2 ? (
                                      <span className="font-bold text-purple-600">50% (₹{(pkg.price * 0.5).toLocaleString('en-IN')})</span>
                                    ) : (
                                      <span className="text-slate-400">-</span>
                                    )}
                                  </td>
                                  <td className="p-2.5 pr-3 font-bold text-slate-800">
                                    {pkg.level === 1
                                      ? '₹2,400'
                                      : `₹${(pkg.teamSize * pkg.price * 0.5).toLocaleString('en-IN')}`}
                                  </td>
                                </tr>
                              ))}
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>
        )}

        {/* ====================================================
            TAB 2: CODE EXPLORER
        ==================================================== */}
        {activeTab === 'files' && (
          <div className="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div className="md:col-span-4 bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-3">
              <div className="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400 pb-2 border-b border-slate-800">
                <FolderTree className="w-4 h-4 text-amber-400" /> Part 7 &amp; Core PHP Files
              </div>

              <div className="space-y-1 text-xs font-mono">
                {[
                  { file: 'admin/members.php', label: 'admin/members.php', tag: 'Part 7 Members' },
                  { file: 'admin/kyc.php', label: 'admin/kyc.php', tag: 'Part 7 KYC Admin' },
                  { file: 'admin/settings.php', label: 'admin/settings.php', tag: 'Part 7 Settings' },
                  { file: 'admin/support.php', label: 'admin/support.php', tag: 'Part 7 Admin Helpdesk' },
                  { file: 'user/support.php', label: 'user/support.php', tag: 'Part 7 User Tickets' },
                  { file: 'user/notifications.php', label: 'user/notifications.php', tag: 'Part 7 Alerts' },
                  { file: 'database/part7_update.sql', label: 'database/part7_update.sql', tag: 'Part 7 DB Migration' },
                  { file: 'user/wallet.php', label: 'user/wallet.php', tag: 'Part 6 Wallet' },
                  { file: 'user/withdraw.php', label: 'user/withdraw.php', tag: 'Part 6 Payout' },
                  { file: 'admin/withdrawals.php', label: 'admin/withdrawals.php', tag: 'Part 6 Admin' },
                  { file: 'includes/income-engine.php', label: 'includes/income-engine.php', tag: 'Part 5 Engine' },
                  { file: 'user/income.php', label: 'user/income.php', tag: 'Part 5 Income' },
                  { file: 'user/binary-tree.php', label: 'user/binary-tree.php', tag: 'Part 4 Tree' },
                  { file: 'includes/functions.php', label: 'includes/functions.php', tag: 'Functions' },
                  { file: 'register.php', label: 'register.php', tag: 'Direct Income Hook' },
                  { file: 'user/dashboard.php', label: 'user/dashboard.php', tag: 'Income Shortcuts' },
                  { file: 'user/includes/sidebar.php', label: 'user/includes/sidebar.php', tag: 'Sidebar Income Link' },
                  { file: 'admin/includes/sidebar.php', label: 'admin/includes/sidebar.php', tag: 'Admin Income Link' },
                ].map((item) => (
                  <button
                    key={item.file}
                    onClick={() => setSelectedFile(item.file)}
                    className={`w-full text-left px-3 py-2 rounded-lg flex items-center justify-between transition ${
                      selectedFile === item.file
                        ? 'bg-sky-500/20 text-sky-300 border border-sky-500/40'
                        : 'text-slate-400 hover:bg-slate-900 hover:text-slate-200'
                    }`}
                  >
                    <span className="truncate">{item.label}</span>
                    <span className="text-[10px] font-sans px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">
                      {item.tag}
                    </span>
                  </button>
                ))}
              </div>
            </div>

            <div className="md:col-span-8 bg-slate-950 p-4 rounded-xl border border-slate-800 flex flex-col">
              <div className="flex items-center justify-between pb-3 mb-3 border-b border-slate-800">
                <div className="flex items-center gap-2">
                  <FileCode className="w-4 h-4 text-sky-400" />
                  <span className="font-mono text-sm font-bold text-white">
                    ssdream/{selectedFile}
                  </span>
                </div>
                <button
                  onClick={() => copyText(`ssdream/${selectedFile}`, 'File Path')}
                  className="text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 px-2.5 py-1 rounded flex items-center gap-1"
                >
                  <Copy className="w-3.5 h-3.5" /> Copy Path
                </button>
              </div>

              <div className="bg-slate-900/90 p-4 rounded-lg font-mono text-xs text-slate-300 overflow-x-auto max-h-[550px] leading-relaxed border border-slate-800">
                <div className="text-slate-500 italic mb-2">
                  // Complete physical file located at: ssdream/{selectedFile}
                </div>
                {selectedFile === 'includes/income-engine.php' && (
                  <pre>{`<?php
/**
 * Social Service Dream - Official Income Calculation Engine
 * =========================================================================
 * PART 5: CORE INCOME CALCULATION & LEDGER DISTRIBUTION
 * File Target: C:\\xampp\\htdocs\\ssdream\\includes\\income-engine.php
 *
 * 3 LOCKED BUSINESS RULES:
 * 1. Direct Income = 100% (On Level 1 referral entry, ₹1,200)
 * 2. Level Income = 50% (Level 2 to Level 7 - USER MUST UPGRADE TO BE ELIGIBLE)
 * 3. Sponsor Income = 50% (Level 2 to Unlimited Level)
 */

function calculateDirectIncome(int $newUserId, ?PDO $pdo = null): ?int {
    // Credits 100% (₹1,200) to direct sponsor on joining
    // Updates wallets balance and adds transaction + income ledger records
}

function processUpgradeIncomeDistribution(int $userId, int $toLevel, int $upgradeId, ?PDO $pdo = null): array {
    // 1. Level Income (50%): Finds binary ancestor at distance == toLevel
    //    CRITICAL CHECK: Ancestor's current_level MUST BE >= toLevel
    // 2. Sponsor Income (50%): Finds direct sponsor of upgrading member
    // 3. Atomically credits wallets and logs to incomes & transactions
}
`}</pre>
                )}
                {selectedFile === 'user/income.php' && (
                  <pre>{`<?php
/**
 * Social Service Dream - User Income Ledger & Breakdown
 * =========================================================================
 * PART 5: Official SS Dream Income Stream Reports
 * Target: C:\\xampp\\htdocs\\ssdream\\user\\income.php
 */
requireUserLogin();
$userId = (int)$_SESSION['user_id'];
$typeFilter = strtolower(trim($_GET['type'] ?? 'all'));
$summary = getUserIncomeSummary($userId);
$ledgerData = getUserPaginatedIncomes($userId, $page, 15, $typeFilter);
// Render 4 income metric cards, stream filters, and responsive earnings table
`}</pre>
                )}
                {selectedFile === 'admin/incomes.php' && (
                  <pre>{`<?php
/**
 * Social Service Dream - Admin Income Distribution Audit & Engine Oversight
 * =========================================================================
 * PART 5: Master System Income Monitoring & Audit Ledger
 * Target: C:\\xampp\\htdocs\\ssdream\\admin\\incomes.php
 */
requireAdminLogin();
$metrics = getAdminIncomeMetrics();
$ledgerData = getAdminPaginatedIncomes($page, 20, $typeFilter, $search);
// Complete platform-wide income distribution monitoring, CSV export, and rule matrix
`}</pre>
                )}
                {selectedFile === 'user/binary-tree.php' && (
                  <pre>{`<?php
/**
 * Social Service Dream - 2x2 Binary Tree Visualization
 * Part 4: 2x2 Binary Tree + Sponsor & Team Management
 */
requireUserLogin();
$loggedInUserId = (int)$_SESSION['user_id'];
$treeRoot = getBinaryTreeHierarchy($targetUserId, 3);
// Visual tree canvas with Zoom In, Zoom Out, Reset, and Available slots.`}</pre>
                )}
                {selectedFile !== 'includes/income-engine.php' && selectedFile !== 'user/income.php' && selectedFile !== 'admin/incomes.php' && selectedFile !== 'user/binary-tree.php' && (
                  <p className="text-slate-400">
                    File implemented in full in <code>ssdream/{selectedFile}</code> with PDO prepared statements, transaction locking, idempotency guards, and zero undefined variables.
                  </p>
                )}
              </div>
            </div>
          </div>
        )}

        {/* ====================================================
            TAB 3: DATABASE SCHEMA
        ==================================================== */}
        {activeTab === 'database' && (
          <div className="space-y-4">
            <div className="bg-slate-950 p-5 rounded-xl border border-slate-800">
              <h3 className="text-base font-bold text-white flex items-center gap-2">
                <Database className="w-5 h-5 text-sky-400" /> Database Schema: <code>ssdream</code> (Part 5 Updated)
              </h3>
              <p className="text-xs text-slate-400 mt-1">
                Part 5 adds the dedicated <code>incomes</code> table with dual foreign key links, audit tracking, and idempotent unique constraint <code>uq_upgrade_income (upgrade_id, income_type, user_id)</code>.
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
              {[
                { name: 'support_tickets', desc: 'id, user_id, ticket_no, subject, category, priority, status (open/in_progress/resolved/closed), created_at, updated_at' },
                { name: 'support_messages', desc: 'id, ticket_id, sender_type (user/admin), sender_id, sender_name, message, attachment_path, created_at' },
                { name: 'kyc', desc: 'user_id, document_type, document_number, pan_card_path, status (pending/approved/rejected), admin_notes, reviewed_by, reviewed_at' },
                { name: 'settings', desc: 'setting_key, setting_value, description (min_withdrawal, tds_percent, admin_charge_percent, company_bank_name, etc.)' },
                { name: 'incomes', desc: 'id, user_id, income_type (direct/level/sponsor), amount, percentage, from_user_id, source_level, upgrade_id, status' },
                { name: 'wallets', desc: 'balance, total_credit, total_debit, total_income, total_withdrawn (auto-credited by Part 5 engine, deducted by Part 6 payouts)' },
                { name: 'transactions', desc: 'wallet_id, user_id, transaction_type (credit/debit), category, amount, balance_before, balance_after, status' },
                { name: 'withdrawals', desc: 'id, user_id, amount, tds_deduction, admin_charge, net_amount, payment_mode, payment_details, status' },
                { name: 'users', desc: 'sponsor_id, parent_id, position (left/right), current_level (1-7), status (active/blocked/pending)' },
              ].map((table) => (
                <div key={table.name} className="bg-slate-950 p-4 rounded-xl border border-slate-800">
                  <div className="font-mono font-bold text-amber-400 mb-1">{table.name}</div>
                  <div className="text-slate-400">{table.desc}</div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* ====================================================
            TAB 4: TESTING GUIDE (XAMPP / cPanel)
        ==================================================== */}
        {activeTab === 'setup' && (
          <div className="bg-slate-950 p-6 rounded-2xl border border-slate-800 space-y-5 text-xs text-slate-300">
            <h3 className="text-base font-bold text-white flex items-center gap-2">
              <Server className="w-5 h-5 text-sky-400" /> Complete Testing Guide for Part 5 Income Engine in XAMPP
            </h3>

            <div className="space-y-4">
              <div className="p-4 bg-slate-900 rounded-xl border border-slate-800 space-y-1.5">
                <div className="font-bold text-sky-400">1. Apply Part 5 Database Migration</div>
                <p>
                  Open phpMyAdmin at <code>http://localhost/phpmyadmin</code> &rarr; select database <code>ssdream</code>.<br />
                  Import file <code>C:\xampp\htdocs\ssdream\database\part5_update.sql</code> to create the <code>incomes</code> ledger table and idempotency indexes.
                </p>
              </div>

              <div className="p-4 bg-slate-900 rounded-xl border border-slate-800 space-y-1.5">
                <div className="font-bold text-sky-400">2. Test Rule 1: 100% Direct Income on Level 1 Registration</div>
                <p>
                  Visit <code>http://localhost/ssdream/register.php</code>.<br />
                  Register a new member with Sponsor ID = <code>rohit99</code>.<br />
                  &bull; <strong>Result:</strong> Member is activated at Level 1 (₹1,200).<br />
                  &bull; <strong>Verification:</strong> Sponsor <code>rohit99</code> instantly receives ₹1,200 (100% of ₹1,200) in wallet balance, with an entry in <code>incomes</code> (type = <code>direct</code>).
                </p>
              </div>

              <div className="p-4 bg-slate-900 rounded-xl border border-slate-800 space-y-1.5">
                <div className="font-bold text-sky-400">3. Test Critical Rule: User Upgrade Check Before Level Income</div>
                <p>
                  &bull; <strong>Test Case Ineligible:</strong> A downline member at Level 2 in <code>rohit99</code>'s tree submits an upgrade to Level 3 (₹3,000).<br />
                  If <code>rohit99</code> is currently only at Level 2, the engine strictly checks: <em>current_level (2) &lt; to_level (3)</em>. <code>rohit99</code> will <strong>NOT</strong> receive Level 3 Income.<br />
                  &bull; <strong>Test Case Eligible:</strong> Once <code>rohit99</code> upgrades to Level 3, any subsequent Level 3 downline upgrade will credit ₹1,500 (50% of ₹3,000) Level Income to <code>rohit99</code>!
                </p>
              </div>

              <div className="p-4 bg-slate-900 rounded-xl border border-slate-800 space-y-1.5">
                <div className="font-bold text-sky-400">4. Test Rule 3: 50% Sponsor Income (Level 2 to Unlimited Level)</div>
                <p>
                  When member upgrades to Level 2 (₹2,000), their direct sponsor receives 50% = ₹1,000 Sponsor Income.<br />
                  When member upgrades to Level 3 (₹3,000), direct sponsor receives 50% = ₹1,500 Sponsor Income.<br />
                  All distributions happen inside PDO transactions with rollback safeguards.
                </p>
              </div>

              <div className="p-4 bg-slate-900 rounded-xl border border-slate-800 space-y-1.5">
                <div className="font-bold text-sky-400">5. Verify User &amp; Admin Income Pages</div>
                <p>
                  &bull; <strong>User Income Center:</strong> Log in as member &rarr; visit <code>http://localhost/ssdream/user/income.php</code>. Check earnings breakdown by stream (Direct, Level, Sponsor) and transaction logs.<br />
                  &bull; <strong>Admin Income Audit:</strong> Log in as admin &rarr; visit <code>http://localhost/ssdream/admin/incomes.php</code>. Monitor platform totals, inspect any user's earnings, and verify engine integrity.
                </p>
              </div>
            </div>
          </div>
        )}
      </main>

      {/* Footer */}
      <footer className="border-t border-slate-800 bg-slate-950 px-4 py-3 text-center text-xs text-slate-500">
        Social Service Dream &bull; Part 5: Official Income Calculation Engine (100% Direct, 50% Level 2&ndash;7, 50% Sponsor L2&ndash;Unlimited) &bull; Fully Operational
      </footer>
    </div>
  );
}

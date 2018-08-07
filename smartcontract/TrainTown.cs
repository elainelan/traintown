using Neo.SmartContract.Framework;
using Neo.SmartContract.Framework.Services.Neo;
using Neo.SmartContract.Framework.Services.System;
using Helper = Neo.SmartContract.Framework.Helper;

using System;
using System.ComponentModel;
using System.Numerics;


namespace TrainTown
{
    /**
     * smart contract for Gladiator
     * @author Clyde
     */
    public class townContract : SmartContract
    {
        /**
         * 角斗士属性结构数据
         */
        [Serializable]
        public class NFTInfo
        {
            
            public byte[] owner; 
			
			// TO DO ： add asset info

        }

        public class TransferInfo
        {
            public byte[] from;
            public byte[] to;
            public BigInteger value;
        }

        // notify 转账通知
        public delegate void deleTransfer(byte[] from, byte[] to, BigInteger value);
        [DisplayName("transfer")]
        public static event deleTransfer Transferred;

        // 合约拥有者，超级管理员
        public static readonly byte[] ContractOwner = "AcKA1A3TRx6ubNzi3Dz2QFW6V9uEkeVasg".ToScriptHash();
        // 有权限发布0代角斗士的钱包地址
        public static readonly byte[] MintOwner = "AcKA1A3TRx6ubNzi3Dz2QFW6V9uEkeVasg".ToScriptHash();

        // 名称
        public static string Name() => "CrazyGladiator";
        // 符号
        public static string Symbol() => "CGL";

        // 存储已发行的key
        private const string KEY_TOTAL = "totalSupply";
        // 发行总量的key
        private const string KEY_ALL = "allSupply";
        //发行总量
        private const ulong ALL_SUPPLY_CG = 4320;
        //版本
        public static string Version() => "1.0.16";

        public static byte[] ownerOf(BigInteger tokenId)
        {
            object[] objInfo = _getNFTInfo(tokenId.AsByteArray());
            NFTInfo info = (NFTInfo)(object) objInfo;
            if (info.owner.Length>0)
            {
                return info.owner;
            }
            else
            {
                //return System.Text.Encoding.ASCII.GetBytes("token does not exist");
                return new byte[] { };
            }
        }

        public static BigInteger totalSupply()
        {
            return Storage.Get(Storage.CurrentContext, KEY_TOTAL).AsBigInteger();
        }

        public static BigInteger allSupply()
        {
            return Storage.Get(Storage.CurrentContext, KEY_ALL).AsBigInteger();
        }

        public static string tokenURI(BigInteger tokenId)
        {
            return "uri/" + tokenId;
        }


        public static bool transfer(byte[] from, byte[] to, BigInteger tokenId)
        {
            if (from.Length != 20|| to.Length != 20)
            {
                return false;
            }

            StorageContext ctx = Storage.CurrentContext;

            if (from == to)
            {
                //Runtime.Log("Transfer to self!");
                return true;
            }

            object[] objInfo = _getNFTInfo(tokenId.AsByteArray());
            if(objInfo.Length == 0)
            {
                return false;
            }

            NFTInfo info = (NFTInfo)(object)objInfo;
            byte[] ownedBy = info.owner;

            if (from != ownedBy)
            {
                //Runtime.Log("Token is not owned by tx sender");
                return false;
            }
            //bool res = _subOwnerToken(from, tokenId);
            //if (!res)
            //{
            //    //Runtime.Log("Unable to transfer token");
            //    return false;
            //}
            //_addOwnerToken(to, tokenId);

            info.owner = to;
            _putNFTInfo(tokenId.AsByteArray(), info);

            //remove any existing approvals for this token
            byte[] approvalKey = "apr/".AsByteArray().Concat(tokenId.AsByteArray());
            Storage.Delete(ctx, approvalKey);

            //记录交易信息
            _setTxInfo(from, to, tokenId);

            Transferred(from, to, tokenId);
            return true;

        }

        public static bool transferFrom(byte[] tokenFrom, byte[] tokenTo, BigInteger tokenId)
        {
            if (tokenFrom.Length != 20)
            {
                return false;
            }
            if (tokenTo.Length != 20)
            {
                return false;
            }

            if (tokenFrom == tokenTo)
            {
                Runtime.Log("Transfer to self!");
                return true;
            }

            object[] objInfo = _getNFTInfo(tokenId.AsByteArray());
            if (objInfo.Length == 0)
            {
                return false;
            }

            NFTInfo info = (NFTInfo)(object)objInfo;

            byte[] tokenOwner = info.owner;
            if (tokenOwner.Length != 20)
            {
                Runtime.Log("Token does not exist");
                return false;
            }
            if (tokenFrom != tokenOwner)
            {
                Runtime.Log("From address is not the owner of this token");
                return false;
            }

            byte[] approvalKey = "apr/".AsByteArray().Concat(tokenId.AsByteArray());
            byte[] authorizedSpender = Storage.Get(Storage.CurrentContext, approvalKey);

            if (authorizedSpender.Length == 0)
            {
                Runtime.Log("No approval exists for this token");
                return false;
            }

            if (Runtime.CheckWitness(authorizedSpender))
            {
                //bool res = _subOwnerToken(tokenFrom, tokenId);
                //if (res == false)
                //{
                //    Runtime.Log("Unable to transfer token");
                //    return false;
                //}
                //_addOwnerToken(tokenTo, tokenId);

                info.owner = tokenTo;
                _putNFTInfo(tokenId.AsByteArray(), info);

                // remove the approval for this token
                Storage.Delete(Storage.CurrentContext, approvalKey);

                Runtime.Log("Transfer complete");

                //记录交易信息
                _setTxInfo(tokenFrom, tokenTo, tokenId);

                Transferred(tokenFrom, tokenTo, tokenId);
                return true;
            }

            Runtime.Log("Transfer by tx sender not approved by token owner");
            return false;
        }

        public static NFTInfo tokenData(BigInteger tokenId)
        {
            object[] objInfo = _getNFTInfo(tokenId.AsByteArray());
            NFTInfo info = (NFTInfo)(object)objInfo;
            return info;
        }

        public static byte[] getAllSupply()
        {
            return Storage.Get(Storage.CurrentContext, "auction");
        }

        public static byte[] getAuctionAddr()
        {
            return Storage.Get(Storage.CurrentContext, "auction");
        }

        public static bool setAuctionAddr(byte[] auctionAddr)
        {
            if (Runtime.CheckWitness(ContractOwner))
            {
                Storage.Put(Storage.CurrentContext, "auction", auctionAddr);
                Storage.Put(Storage.CurrentContext, KEY_ALL, ALL_SUPPLY_CG);
                return true;
            }
            return false;
        }

        public static bool approve(byte[] approved, BigInteger tokenId)
        {
            if (approved.Length != 20)
            {
                return false;
            }

            object[] objInfo = _getNFTInfo(tokenId.AsByteArray());
            NFTInfo info = (NFTInfo)(object)objInfo;

            byte[] tokenOwner = info.owner;
            if (tokenOwner.Length != 20)
            {
                Runtime.Log("Token does not exist");
                return false;
            }

            if (Runtime.CheckWitness(tokenOwner))
            {
                string approvalKey = "apr/" + tokenId;

                // only one third-party spender can be approved
                // at any given time for a specific token

                Storage.Put(Storage.CurrentContext, approvalKey, approved);
                Approved(tokenOwner, approved, tokenId);

                return true;
            }

            Runtime.Log("Incorrect permission");
            return false;
        }

        public static Object Main(string operation, params object[] args)
        {
            if (Runtime.Trigger == TriggerType.Verification)
            {
                if (ContractOwner.Length == 20)
                {
                    // if param ContractOwner is script hash
                    //return Runtime.CheckWitness(ContractOwner);
                    return false;
                }
                else if (ContractOwner.Length == 33)
                {
                    // if param ContractOwner is public key
                    byte[] signature = operation.AsByteArray();
                    return VerifySignature(signature, ContractOwner);
                }
            }
            else if (Runtime.Trigger == TriggerType.VerificationR)
            {
                return true;
            }
            else if (Runtime.Trigger == TriggerType.Application)
            {
                //必须在入口函数取得callscript，调用脚本的函数，也会导致执行栈变化，再取callscript就晚了
                var callscript = ExecutionEngine.CallingScriptHash;
                if (operation == "version") return Version();
                if (operation == "name") return Name();
                if (operation == "symbol") return Symbol();
                if (operation == "decimals") return 0; // NFT can't divide, decimals allways zero
                if (operation == "totalSupply") return totalSupply();

                if (operation == "hasExtraData") return false;
                if (operation == "isEnumable") return false;
                if (operation == "hasBroker") return false;

                if (operation == "ownerOf")
                {
                    BigInteger tokenId = (BigInteger)args[0];
                    return ownerOf(tokenId);
                }

                if (operation == "transfer")
                {
                    if (args.Length != 3)
                        return false;

                    byte[] from = (byte[])args[0];
                    byte[] to = (byte[])args[1];
                    BigInteger tokenId = (BigInteger)args[2];

                    //没有from签名，不让转
                    if (!Runtime.CheckWitness(from))
                    {
                        return false;
                    }
                    //如果有跳板调用，不让转
                    if (ExecutionEngine.EntryScriptHash.AsBigInteger() != callscript.AsBigInteger())
                    {
                        return false;
                    }
                    return transfer(from, to, tokenId);
                }

                if (operation == "transferFrom_app")
                {
                    if (args.Length != 3)
                        return false;

                    byte[] from = (byte[])args[0];
                    byte[] to = (byte[])args[1];
                    BigInteger tokenId = (BigInteger)args[2];

                    //没有from签名，不让转
                    if (!Runtime.CheckWitness(from))
                    {
                        return false;
                    }
                    byte[] auctionAddr = Storage.Get(Storage.CurrentContext, "auction");
                    if(callscript.AsBigInteger() != auctionAddr.AsBigInteger())
                    {
                        return false;
                    }
                    return transfer(from, to, tokenId);
                }

                if (operation == "transfer_app")
                {
                    if (args.Length != 3)
                        return false;

                    byte[] from = (byte[])args[0];
                    byte[] to = (byte[])args[1];
                    BigInteger tokenId = (BigInteger)args[2];

                    //如果from 不是 传入脚本 不让转
                    if (from.AsBigInteger() != callscript.AsBigInteger())
                        return false;

                    return transfer(from, to, tokenId);
                }

                if (operation == "approve")
                {
                    byte[] approved = (byte[])args[0];
                    BigInteger tokenId = (BigInteger)args[1];

                    return approve(approved, tokenId);
                }

                if (operation == "tokenData")
                {
                    BigInteger tokenId = (BigInteger)args[0];

                    return tokenData(tokenId);
                }

                if (operation == "getTXInfo")
                {
                    if (args.Length != 1)
                        return 0;
                    byte[] txid = (byte[])args[0];
                    return getTXInfo(txid);
                }
				
                if (operation == "getAuctionAddr")
                {
                    return getAuctionAddr();
                }

                if (operation == "getAllSupply")
                {
                    return getAllSupply();
                }

                if (operation == "setAuctionAddr")
                {
                    if (args.Length != 1) return 0;
                    byte[] addr = (byte[])args[0];

                    return setAuctionAddr(addr);
                }

                if (operation == "transferFrom")
                {
                    if (args.Length != 3)
                        return false;

                    byte[] from = (byte[])args[0];
                    byte[] to = (byte[])args[1];
                    BigInteger tokenId = (BigInteger)args[2];
                    return transferFrom(from, to, tokenId);
                }

                if (operation == "tokenURI")
                {
                    BigInteger tokenId = (BigInteger)args[0];
                    return tokenURI(tokenId);
                }


                //if (operation == "tokenExtraData")
                //{
                //    BigInteger tokenId = (BigInteger)args[0];
                //    string key = (string)args[1];
                //    return TokenExtraData(tokenId, key);
                //}

                if (operation == "balanceOf")
                {
                    if (args.Length != 1) return 0;
                    byte[] owner = (byte[])args[0];
                    return balanceOf(owner);
                }

                if (operation == "tokensOfOwner")
                {
                    byte[] owner = (byte[])args[0];

                    return tokensOfOwner(owner);
                }

                if (operation == "tokenOfOwnerByIndex")
                {
                    byte[] owner = (byte[])args[0];
                    BigInteger index = (BigInteger)args[1];

                    return tokenOfOwnerByIndex(owner, index);
                }

                if (operation == "allowance")
                {
                    BigInteger tokenId = (BigInteger)args[0];

                    return allowance(tokenId);
                }
                if (operation == "upgrade")//合约的升级就是在合约中要添加这段代码来实现
                {
                    //不是管理员 不能操作
                    if (!Runtime.CheckWitness(ContractOwner))
                        return false;

                    if (args.Length != 1 && args.Length != 9)
                        return false;

                    byte[] script = Blockchain.GetContract(ExecutionEngine.ExecutingScriptHash).Script;
                    byte[] new_script = (byte[])args[0];
                    //如果传入的脚本一样 不继续操作
                    if (script == new_script)
                        return false;

                    byte[] parameter_list = new byte[] { 0x07, 0x10 };
                    byte return_type = 0x05;
                    bool need_storage = (bool)(object)05;
                    string name = "NFT";
                    string version = "1.1";
                    string author = "CG";
                    string email = "0";
                    string description = "test";

                    if (args.Length == 9)
                    {
                        parameter_list = (byte[])args[1];
                        return_type = (byte)args[2];
                        need_storage = (bool)args[3];
                        name = (string)args[4];
                        version = (string)args[5];
                        author = (string)args[6];
                        email = (string)args[7];
                        description = (string)args[8];
                    }
                    Contract.Migrate(new_script, parameter_list, return_type, need_storage, name, version, author, email, description);
                    return true;
                }
                //if (operation == "tokenByIndex")
                //{
                //    BigInteger index = (BigInteger)args[0];
                //    return TokenByIndex(index);
                //}

                //if (operation == "approveBroker")
                //{
                //    byte[] owner = (byte[])args[0];
                //    byte[] broker = (byte[])args[1];
                //    bool isApproved = (bool)args[2];

                //    return ApproveBroker(owner, broker, isApproved);
                //}

                //if (operation == "brokerOfOwner")
                //{
                //    byte[] owner = (byte[])args[0];

                //    return brokerOfOwner(owner);
                //}

                //if (operation == "modifyURIBase")
                //{
                //    string uriBase = (string)args[0];

                //    return modifyURIBase(uriBase);
                //}

                //if (operation == "modifyExtraData")
                //{
                //    BigInteger tokenId = (BigInteger)args[0];
                //    string key = (string)args[1];
                //    Object extraData = (Object)args[2];

                //    return modifyExtraData(tokenId, key, extraData);
                //}

                //if (operation == "delExtraData")
                //{
                //    BigInteger tokenId = (BigInteger)args[0];
                //    string key = (string)args[1];

                //    return DelExtraData(tokenId, key);
                //}

            }

            return false;
        }

        private static object[] _getNFTInfo(byte[] tokenId)
        {

            byte[] v = Storage.Get(Storage.CurrentContext, tokenId);
            if (v.Length == 0)
                return new object[0];

            return (object[])Helper.Deserialize(v);
            // return Helper.Deserialize(v) as TransferInfo;
        }

        private static void _putNFTInfo(byte[] tokenId, NFTInfo info)
        {
            byte[] nftInfo = Helper.Serialize(info);

            Storage.Put(Storage.CurrentContext, tokenId, nftInfo);
        }

        public static object[] getTXInfo(byte[] txid)
        {
            byte[] v = Storage.Get(Storage.CurrentContext, txid);
            if (v.Length == 0)
                return new object[0];

            return (object[])Helper.Deserialize(v);
            // return Helper.Deserialize(v) as TransferInfo;
        }

        private static void _setTxInfo(byte[] from, byte[] to, BigInteger value)
        {
            TransferInfo info = new TransferInfo();
            info.from = from;
            info.to = to;
            info.value = value;

            byte[] txinfo = Helper.Serialize(info);

            byte[] txid = (ExecutionEngine.ScriptContainer as Transaction).Hash;

            Storage.Put(Storage.CurrentContext, txid, txinfo);
        }

        public static Object tokenExtraData(BigInteger tokenId, string key)
        {
            return null;
        }

        public static BigInteger balanceOf(byte[] owner)
        {
            return Storage.Get(Storage.CurrentContext, owner).AsBigInteger();
        }

        public static BigInteger tokenOfOwnerByIndex(byte[] owner, BigInteger index)
        {
            byte[] ownerKey = owner.Concat(index.AsByteArray());
            return Storage.Get(Storage.CurrentContext, ownerKey).AsBigInteger();
        }

        public static byte[] allowance(BigInteger tokenId)
        {
            byte[] approvalKey = "apr/".AsByteArray().Concat(tokenId.AsByteArray());
            return Storage.Get(Storage.CurrentContext, approvalKey);
        }

        public static BigInteger[] tokensOfOwner(byte[] owner)
        {
            BigInteger tokenCount = balanceOf(owner);
            BigInteger[] result = new BigInteger[(int)tokenCount];

            if (tokenCount == 0)
            {
                // Return an empty array
                return result;
            }
            else
            {
                // We count on the fact that all NFTInfo have IDs starting at 1 and increasing
                // sequentially up to the totalCat count.
                BigInteger idx;
                for (idx = 1; idx < tokenCount + 1; idx += 1)
                {
                    byte[] ownerKey = owner.Concat(idx.AsByteArray());
                    byte[] tokenId = Storage.Get(Storage.CurrentContext, ownerKey);

                    //result.Concat(_byteLen(tokenId.Length)).Concat(tokenId);
                    result[(int)idx - 1] = tokenId.AsBigInteger();
                }

                return result;
            }

        }

    }
}

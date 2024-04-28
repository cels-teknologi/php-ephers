<?php

namespace Ephers\Ethereum\Contracts;

use Ephers\Ethereum\Abi\Fragments\FunctionFragment;
use Ephers\Providers\Provider;

class ContractMethod
{
    public function __construct(
        protected Contract $contract,
        protected string $name,
        protected FunctionFragment $fragment,
        protected array $args = [],
    ) { }

    /**
     * @todo  Assert that provider can execute given methods
     */
    public function assertProviderExecutionAbility(string $method): bool
    {
        return true;
    }

    public function populateTransaction()
    {
        return [
            'to' => $this->contract->address->toHex(),
            'data' => $this->contract->interface->encodeFunctionData(
                $this->fragment,
                $this->args,
            ),
        ];
    }

    public function staticCall()
    {
        $result = $this->_staticCall();
        return $result;
    }

    public function send()
    {
        // const runner = contract.runner;
        // assert(canSend(runner), "contract runner does not support sending transactions",
        //     "UNSUPPORTED_OPERATION", { operation: "sendTransaction" });

        // const tx = await runner.sendTransaction(await populateTransaction(...args));
        // const provider = getProvider(contract.runner);
        // // @TODO: the provider can be null; make a custom dummy provider that will throw a
        // // meaningful error
        // return new ContractTransactionResponse(contract.interface, <Provider>provider, tx);
    }

    public function estimateGas()
    {
        // const runner = getRunner(contract.runner, "estimateGas");
        // assert(canEstimate(runner), "contract runner does not support gas estimation",
        //     "UNSUPPORTED_OPERATION", { operation: "estimateGas" });

        // return await runner.estimateGas(await populateTransaction(...args));
    }

    protected function _staticCall()
    {
        if (!$this->assertProviderExecutionAbility('eth_call')) {
            throw new \RuntimeException('Provider does not support `eth_call`');
        }

        return $this->contract->provider->send('eth_call', [
            $this->populateTransaction(),
            'latest', // TODO: probably the ability to choose blocks
        ]);

        // let result = "0x";
        // try {
        //     result = await runner.call(tx);
        // } catch (error: any) {
        //     if (isCallException(error) && error.data) {
        //         throw contract.interface.makeError(error.data, tx);
        //     }
        //     throw error;
        // }

        // const fragment = getFragment(...args);
        // return contract.interface.decodeFunctionResult(fragment, result);
    }

    public function __invoke() {
        if ($this->fragment->constant) {
            return $this->staticCall();
        }

        return $this->send();
    }
}
